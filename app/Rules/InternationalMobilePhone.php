<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class InternationalMobilePhone implements ValidationRule
{
    private ?int $ignoreUserId;

    public function __construct(?int $ignoreUserId = null)
    {
        $this->ignoreUserId = $ignoreUserId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        try {
            $validator = Validator::make(
                [$attribute => $value],
                [$attribute => 'phone:AUTO']
            );

            if ($validator->fails()) {
                $fail('The :attribute must be a valid international phone number (e.g., +1234567890).');
                return;
            }
        } catch (\Exception $e) {
            $fail('The :attribute must be a valid international phone number (e.g., +1234567890).');
            return;
        }

        if (!str_starts_with($value, '+')) {
            $fail('The :attribute must start with a + sign.');
            return;
        }

        $query = User::where('mobile_number', $value);
        if ($this->ignoreUserId) {
            $query->where('id', '!=', $this->ignoreUserId);
        }

        if ($query->exists()) {
            $fail('The :attribute has already been taken.');
        }
    }

    public static function forCreate(): self
    {
        return new self();
    }

    public static function forUpdate(int $userId): self
    {
        return new self($userId);
    }
}