<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Address\GeoCoder;
use AppBundle\Entity\Jecoute\Survey;
use AppBundle\Entity\Jecoute\SurveyQuestion;
use AppBundle\Entity\Projection\ReferentManagedUser;
use AppBundle\Entity\ReferentOrganizationalChart\PersonOrganizationalChartItem;
use AppBundle\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use AppBundle\Event\EventCommand;
use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Form\EventCommandType;
use AppBundle\Form\ReferentPersonLinkType;
use AppBundle\Form\Jecoute\SurveyFormType;
use AppBundle\Jecoute\StatisticsExporter;
use AppBundle\Jecoute\StatisticsProvider;
use AppBundle\Referent\ManagedCommitteesExporter;
use AppBundle\Referent\ManagedEventsExporter;
use AppBundle\Referent\ManagedUsersFilter;
use AppBundle\Referent\SurveyExporter;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\EventRepository;
use AppBundle\Repository\Jecoute\DataAnswerRepository;
use AppBundle\Repository\ReferentOrganizationalChart\OrganizationalChartItemRepository;
use AppBundle\Repository\ReferentOrganizationalChart\ReferentPersonLinkRepository;
use AppBundle\Repository\ReferentRepository;
use AppBundle\Repository\SuggestedQuestionRepository;
use AppBundle\Repository\Jecoute\SurveyRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/espace-referent")
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ReferentController extends Controller
{
    public const TOKEN_ID = 'referent_managed_users';

    /**
     * @Route("/utilisateurs", name="app_referent_users")
     * @Method("GET")
     */
    public function usersAction(Request $request): Response
    {
        $filter = new ManagedUsersFilter();
        $filter->handleRequest($request);

        if ($filter->hasToken() && !$this->isCsrfTokenValid(self::TOKEN_ID, $filter->getToken())) {
            return $this->redirectToRoute('app_referent_users');
        }

        $repository = $this->getDoctrine()->getRepository(ReferentManagedUser::class);
        $results = $repository->search($this->getUser(), $filter->hasToken() ? $filter : null);

        $filter->setToken($this->get('security.csrf.token_manager')->getToken(self::TOKEN_ID));

        return $this->render('referent/users_list.html.twig', [
            'filter' => $filter,
            'has_filter' => $request->query->has(ManagedUsersFilter::PARAMETER_TOKEN),
            'results_count' => $results->count(),
            'results' => $results->getQuery()->getResult(),
        ]);
    }

    /**
     * @Route("/evenements", name="app_referent_events")
     * @Method("GET")
     */
    public function eventsAction(EventRepository $eventRepository, ManagedEventsExporter $eventsExporter): Response
    {
        return $this->render('referent/events_list.html.twig', [
            'managedEventsJson' => $eventsExporter->exportAsJson($eventRepository->findManagedBy($this->getUser())),
        ]);
    }

    /**
     * @Route("/evenements/creer", name="app_referent_events_create")
     * @Method("GET|POST")
     */
    public function eventsCreateAction(Request $request, GeoCoder $geoCoder): Response
    {
        $command = new EventCommand($this->getUser());
        $command->setTimeZone($geoCoder->getTimezoneFromIp($request->getClientIp()));
        $form = $this->createForm(EventCommandType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->get('app.event.handler')->handle($command);

            $registrationCommand = new EventRegistrationCommand($event, $this->getUser());
            $this->get('app.event.registration_handler')->handle($registrationCommand);

            $this->addFlash('info', 'referent.event.creation.success');

            return $this->redirectToRoute('app_event_show', [
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->render('referent/event_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/comites", name="app_referent_committees")
     * @Method("GET")
     */
    public function committeesAction(CommitteeRepository $committeeRepository, ManagedCommitteesExporter $committeesExporter): Response
    {
        return $this->render('referent/committees_list.html.twig', [
            'managedCommitteesJson' => $committeesExporter->exportAsJson($committeeRepository->findManagedBy($this->getUser())),
        ]);
    }

    /**
     * @Route("/jecoute/questionnaires", name="app_referent_surveys")
     * @Method("GET")
     */
    public function jecouteSurveysListAction(
        SurveyRepository $surveyRepository,
        SurveyExporter $surveyExporter,
        UserInterface $user
    ): Response {
        return $this->render('referent/surveys/list.html.twig', [
            'surveysListJson' => $surveyExporter->exportAsJson(
                $surveyRepository->findAllByAuthor($user)
            ),
        ]);
    }

    /**
     * @Route("/jecoute/questionnaire/creer", name="app_referent_survey_create")
     * @Method("GET|POST")
     */
    public function jecouteSurveyCreateAction(
        Request $request,
        ObjectManager $manager,
        SuggestedQuestionRepository $suggestedQuestionRepository,
        UserInterface $user
    ): Response {
        $form = $this
            ->createForm(SurveyFormType::class, new Survey($user))
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($form->getData());
            $manager->flush();

            $this->addFlash('info', 'survey.create.success');

            return $this->redirectToRoute('app_referent_surveys');
        }

        return $this->render('referent/surveys/create.html.twig', [
            'form' => $form->createView(),
            'suggestedQuestions' => $suggestedQuestionRepository->findAllPublished(),
        ]);
    }

    /**
     * @Route(
     *     path="/jecoute/questionnaire/{uuid}/editer",
     *     name="app_referent_survey_edit",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     }
     * )
     * @Method("GET|POST")
     */
    public function jecouteSurveyEditAction(
        Request $request,
        Survey $survey,
        ObjectManager $manager,
        SuggestedQuestionRepository $suggestedQuestionRepository
    ): Response {
        $form = $this
            ->createForm(SurveyFormType::class, $survey)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            $this->addFlash('info', 'survey.edit.success');

            return $this->redirectToRoute('app_referent_surveys');
        }

        return $this->render('referent/surveys/create.html.twig', [
            'form' => $form->createView(),
            'suggestedQuestions' => $suggestedQuestionRepository->findAllPublished(),
        ]);
    }

    /**
     * @Route(
     *     path="/jecoute/questionnaire/{uuid}/stats",
     *     name="app_referent_survey_stats",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET"},
     * )
     *
     * @Entity("survey", expr="repository.findOneByUuid(uuid)")
     *
     * @Security("is_granted('IS_AUTHOR_OF', survey)")
     */
    public function jecouteSurveyStatsAction(Survey $survey, StatisticsProvider $provider): Response
    {
        return $this->render('referent/surveys/stats.html.twig', [
            'data' => $provider->getStatsBySurvey($survey),
        ]);
    }

    /**
     * @Route(
     *     path="/jecoute/questionnaire/{uuid}/dupliquer",
     *     name="app_referent_survey_duplicate",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET"},
     * )
     *
     * @Entity("survey", expr="repository.findOneByUuid(uuid)")
     *
     * @Security("is_granted('IS_AUTHOR_OF', survey)")
     */
    public function jecouteSurveyDuplicateAction(Survey $survey, ObjectManager $manager): Response
    {
        $clonedSurvey = clone $survey;

        $manager->persist($clonedSurvey);
        $manager->flush();

        $this->addFlash('info', 'survey.duplicate.success');

        return $this->redirectToRoute('app_referent_surveys');
    }

    /**
     * @Route(
     *     path="/jecoute/question/{uuid}/reponses",
     *     name="app_referent_survey_stats_answers_list",
     *     condition="request.isXmlHttpRequest()",
     *     methods={"GET"},
     * )
     *
     * @Security("is_granted('IS_AUTHOR_OF', surveyQuestion)")
     */
    public function jecouteSurveyAnswersListAction(
        SurveyQuestion $surveyQuestion,
        DataAnswerRepository $dataAnswerRepository
    ): Response {
        return $this->render('referent/surveys/data_answers_dialog_content.html.twig', [
            'answers' => $dataAnswerRepository->findAllBySurveyQuestion($surveyQuestion),
        ]);
    }

    /**
     * @Route("/organigramme", name="app_referent_organizational_chart")
     * @Security("is_granted('IS_ROOT_REFERENT')")
     */
    public function organizationalChartAction(OrganizationalChartItemRepository $organizationalChartItemRepository, ReferentRepository $referentRepository)
    {
        return $this->render('referent/organizational_chart.html.twig', [
            'organization_chart_items' => $organizationalChartItemRepository->getRootNodes(),
            'referent' => $referentRepository->findOneByEmailAndSelectPersonOrgaChart($this->getUser()->getEmailAddress()),
        ]);
    }

    /**
     * @Route("/organigramme/{id}", name="app_referent_referent_person_link_edit")
     * @Security("is_granted('IS_ROOT_REFERENT')")
     */
    public function editReferentPersonLink(Request $request, ReferentPersonLinkRepository $referentPersonLinkRepository, ReferentRepository $referentRepository, PersonOrganizationalChartItem $personOrganizationalChartItem)
    {
        $form = $this->createForm(
            ReferentPersonLinkType::class,
            $referentPersonLinkRepository->findOrCreateByOrgaItemAndReferent(
                $personOrganizationalChartItem,
                $referentRepository->findOneByEmail($this->getUser()->getEmailAddress())
            )
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ReferentPersonLink $referentPersonLink */
            $referentPersonLink = $form->getData();

            $em = $this->getDoctrine()->getManager();

            $em->persist($referentPersonLink);
            $em->flush();

            $this->addFlash('success', 'Organigramme mis à jour.');

            return $this->redirectToRoute('app_referent_organizational_chart');
        }

        return $this->render('referent/edit_referent_person_link.html.twig', [
            'form_referent_person_link' => $form->createView(),
            'person_organizational_chart_item' => $personOrganizationalChartItem,
        ]);
    }

    /**
     * @Route(
     *     path="/jecoute/questionnaire/{uuid}/stats/download",
     *     name="app_referent_survey_stats_download",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET"},
     * )
     *
     * @Entity("survey", expr="repository.findOneByUuid(uuid)")
     *
     * @Security("is_granted('IS_AUTHOR_OF', survey)")
     */
    public function jecouteSurveyStatsDownloadAction(
        Survey $survey,
        StatisticsExporter $statisticsExporter
    ): Response {
        $dataFile = $statisticsExporter->export($survey);

        return new Response($dataFile['content'], Response::HTTP_OK, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment;filename="'.$dataFile['filename'].'"',
        ]);
    }
}
