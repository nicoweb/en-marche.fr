<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentResetPasswordConfirmationMessage extends MailjetMessage
{
    public static function createFromAdherent(Adherent $adherent): self
    {
        return new static(
            Uuid::uuid4(),
            '130495',
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Confirmation de modification de votre mot de passe',
            ['target_firstname' => self::escape($adherent->getFirstName())]
        );
    }
}
