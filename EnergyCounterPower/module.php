<?php

declare(strict_types=1);

class EnergyCounterPower extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        /*
         * 0 = Power (W)
         * 1 = Current (A)
         */
        $this->RegisterPropertyInteger('SourceType', 0);
        $this->RegisterPropertyInteger('SourceVariable', 0);
        $this->RegisterPropertyInteger('Voltage', 230);
        $this->RegisterPropertyInteger('Interval', 60);
        $this->RegisterPropertyBoolean('Backstop', false);

        $this->RegisterTimer('UpdateTimer', 0, 'EZS_Update($_IPS[\'TARGET\']);');

        $this->RegisterVariableFloat('Current', $this->Translate('Current'), 'Watt.3680', 0);
        $this->RegisterVariableFloat('Counter', $this->Translate('Counter'), 'Electricity', 1);

    }

    public function ApplyChanges()
    {

        //Never delete this line!
        parent::ApplyChanges();

        $this->SetTimerInterval('UpdateTimer', $this->ReadPropertyInteger('Interval') * 1000);

        //Delete all registrations in order to readd them
        foreach ($this->GetMessageList() as $senderID => $messages) {
            foreach ($messages as $message) {
                $this->UnregisterMessage($senderID, $message);
            }
        }
        $this->RegisterMessage($this->ReadPropertyInteger('SourceVariable'), VM_UPDATE);

        //Add references
        foreach ($this->GetReferenceList() as $reference) {
            $this->UnregisterReference($reference);
        }
        $sourceID = $this->ReadPropertyInteger('SourceVariable');
        if ($sourceID != 0) {
            $this->RegisterReference($sourceID);
        }
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {

        //guard against messages that were registered from previous configurations
        if ($SenderID == $this->ReadPropertyInteger('SourceVariable')) {
            $this->Update();
        }
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:
     *
     * EZS_Update($id);
     *
     */
    public function Update()
    {
        if (IPS_SemaphoreEnter('EZS_' . $this->InstanceID, 1000)) {
            if (IPS_VariableExists($this->ReadPropertyInteger('SourceVariable'))) {

                //we use the last updated value to calculate the amount the need to add
                $timeDiff = time() - IPS_GetVariable($this->GetIDForIdent('Current'))['VariableUpdated'];

                //get current value -> if Backstop is active and value is negativ set to 0
                $backstop = $this->ReadPropertyBoolean('Backstop');
                $currentValue = GetValue($this->GetIDForIdent('Current'));
                if ($backstop && ($currentValue < 0)) {
                    $currentValue = 0;
                }

                //fetch last counter
                $lastCounterValue = GetValue($this->GetIDForIdent('Counter'));

                //add to our counter
                SetValue($this->GetIDForIdent('Counter'), $lastCounterValue + (($currentValue / 1000) * ($timeDiff / 3600)));

                //fetch source value
                $sourceValue = GetValue($this->ReadPropertyInteger('SourceVariable'));

                //Convert current to power
                if ($this->ReadPropertyInteger('SourceType') == 1) {
                    $sourceValue = $sourceValue * $this->ReadPropertyInteger('Voltage');
                }

                SetValue($this->GetIDForIdent('Current'), $sourceValue);
            }
            IPS_SemaphoreLeave('EZS_' . $this->InstanceID);
        }
    }
}
