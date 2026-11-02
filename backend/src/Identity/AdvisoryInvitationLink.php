<?php

declare(strict_types=1);

namespace App\Identity;

interface AdvisoryInvitationLink
{
    public function url(string $token): string;
}
