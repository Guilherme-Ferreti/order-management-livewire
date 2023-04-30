<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Enums\PasswordStrengthLevel;
use Livewire\Component;
use ZxcvbnPhp\Zxcvbn;

class RegisterPasswords extends Component
{
    public string $password = '';

    public string $passwordConfirmation = '';

    public int $strengthScore = 0;

    public array $strengthLevels = [
        1 => 'Weak',
        2 => 'Fair',
        3 => 'Good',
        4 => 'Strong',
    ];

    public function updatedPassword(string $password): void
    {
        $this->updateStrengthScore($password);
    }

    public function generatePassword(): void
    {
        $lowercase = range('a', 'z');
        $uppercase = range('A', 'Z');
        $digits = range(0, 9);
        $special = ['!', '@', '#', '$', '%', '^', '*'];
        $chars = array_merge($lowercase, $uppercase, $digits, $special);
        $length = 12;

        do {
            $password = [];

            for ($i = 0; $i <= $length; $i++) {
                $int = rand(0, count($chars) - 1);
                $password[] = $chars[$int];
            }

        } while (empty(array_intersect($special, $password)));

        $this->setPasswords(implode('', $password));
    }

    private function setPasswords(string $password): void
    {
        $this->password = $password;
        $this->passwordConfirmation = $password;

        $this->updateStrengthScore($password);
    }

    public function updateStrengthScore(string $password): void
    {
        $this->strengthScore = (new Zxcvbn)->passwordStrength($password)['score'];
    }

    public function getPasswordStrengthLabel(): string
    {
        return PasswordStrengthLevel::tryFrom($this->strengthScore)?->label() ?? PasswordStrengthLevel::WEAK->label();
    }

    public function render()
    {
        return view('livewire.register-passwords');
    }
}
