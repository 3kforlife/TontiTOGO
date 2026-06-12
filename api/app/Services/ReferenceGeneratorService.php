<?php

namespace App\Services;

use App\Models\Contribution;
use App\Models\Member;

class ReferenceGeneratorService
{
    public function generateContributionReference(): string
    {
        $date = now()->format('Ymd');
        $count = Contribution::whereDate('created_at', today())->count() + 1;

        return sprintf('CTR-%s-%05d', $date, $count);
    }

    public function generateMemberCode(): string
    {
        $count = Member::withTrashed()->count() + 1;
        $code  = sprintf('MBR-%06d', $count);

        while (Member::where('member_code', $code)->exists()) {
            $count++;
            $code = sprintf('MBR-%06d', $count);
        }

        return $code;
    }
}
