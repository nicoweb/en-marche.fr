<?php

namespace AppBundle\Controller\EnMarche\AdherentMessage;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-animateur/messagerie", name="app_message_committee_")
 *
 * @Security("is_granted(['ROLE_HOST', 'ROLE_SUPERVISOR'])")
 */
class CommitteeMessageController extends AbstractMessageController
{
    protected function getMessageType(): string
    {
        return AdherentMessageTypeEnum::COMMITTEE;
    }
}
