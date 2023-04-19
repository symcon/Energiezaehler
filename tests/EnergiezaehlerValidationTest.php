<?php

declare(strict_types=1);
include_once __DIR__ . '/stubs/Validator.php';
class EnergiezaehlerValidationTest extends TestCaseSymconValidation
{
    public function testValidateEnergiezaehler(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }
    public function testValidateEnergiezaehlerImpulsModule(): void
    {
        $this->validateModule(__DIR__ . '/../EnergiezaehlerImpuls');
    }
    public function testValidateEnergiezaehlerStromModule(): void
    {
        $this->validateModule(__DIR__ . '/../EnergiezaehlerStrom');
    }
}