@republicansilence
Feature:
  As Referent|Deputy|CP-Host|Committee-Host or Committee-Supervisor
  I cannot communicate with the adherents when one republican silence is declared for the same Referent Tags

  Background:
    Given the following fixtures are loaded:
      | LoadCitizenActionData     |
      | LoadRepublicanSilenceData |
      | LoadDistrictData          |

  Scenario Outline: As referent of department 92 I cannot communicate with adherent from my referent space.
    Given I am logged as "referent@en-marche-dev.fr"
     When I go to "<uri>"
     Then I should see "En raison du silence républicain, votre espace est momentanément désactivé. Vous pourrez de nouveau y accéder à la fin de celui-ci."
    Examples:
      | uri                                   |
      | /espace-referent/utilisateurs         |
      | /espace-referent/evenements/creer     |

  Scenario Outline: As committee host I cannot access to the committee pages
    Given I am logged as "lolodie.dutemps@hotnix.tld"
    When I go to "<uri>"
    Then I should see "En raison du silence républicain, votre espace est momentanément désactivé. Vous pourrez de nouveau y accéder à la fin de celui-ci."
    Examples:
      | uri                                                       |
      | /comites/en-marche-comite-de-singapour                    |
      | /comites/en-marche-comite-de-singapour/evenements/ajouter |

  @javascript
  Scenario: As committee host I cannot access to member contact page
    Given I am logged as "lolodie.dutemps@hotnix.tld"
      And I am on "/comites/en-marche-comite-de-singapour/membres"
      And I check "members[]"
     When I click the "members-contact-button" element
     Then I should be on "/comites/en-marche-comite-de-singapour/membres/contact"
      And I should see "En raison du silence républicain, votre espace est momentanément désactivé. Vous pourrez de nouveau y accéder à la fin de celui-ci."

  Scenario Outline: As CP host I cannot access to the CP pages
    Given I am logged as "lolodie.dutemps@hotnix.tld"
    When I go to "<uri>"
    Then I should see "En raison du silence républicain, votre espace est momentanément désactivé. Vous pourrez de nouveau y accéder à la fin de celui-ci."
    Examples:
      | uri                                                             |
      | /projets-citoyens/13003-en-marche-projet-citoyen/discussions    |
      | /projets-citoyens/13003-en-marche-projet-citoyen/actions/creer  |

  @javascript
  Scenario: As CP host I cannot access to member contact page
    Given I am logged as "lolodie.dutemps@hotnix.tld"
    And I am on "/projets-citoyens/13003-en-marche-projet-citoyen/acteurs"
    And I check "members[]"
    When I click the "members-contact-button" element
    Then I should be on "/projets-citoyens/13003-en-marche-projet-citoyen/acteurs/contact"
    And I should see "En raison du silence républicain, votre espace est momentanément désactivé. Vous pourrez de nouveau y accéder à la fin de celui-ci."

  Scenario: As deputy of FDE-06 (CH and LI) I cannot communicate with adherents from my deputy space.
    Given I am logged as "deputy-ch-li@en-marche-dev.fr"
    When I go to "/espace-depute/utilisateurs/message"
    Then I should see "En raison du silence républicain, votre espace est momentanément désactivé. Vous pourrez de nouveau y accéder à la fin de celui-ci."
