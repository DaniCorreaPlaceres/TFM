<?php

declare(strict_types=1);

namespace App\Identity;

interface AdvisoryClientInvitationLink
{
    public function url(string $token): string;
}
