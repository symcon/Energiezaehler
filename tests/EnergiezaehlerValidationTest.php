<?php

declare(strict_types=1);
include_once __DIR__ . '/stubs/Validator.php';
class EnergiezaehlerValidationTest extends TestCaseSymconValidation
{
    public function testValidateEnergiezaehler(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }
    public function testValidateEnergyCounterPulseModule(): void
    {
        $this->validateModule(__DIR__ . '/../EnergyCounterPulse');
    }
    public function testValidateEnergyCounterPowerModule(): void
    {
        $this->validateModule(__DIR__ . '/../EnergyCounterPower');
    }
}