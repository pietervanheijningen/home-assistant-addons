<?php
/*
 * PHP EpSolar Tracer Class (PhpEpsolarTracer) v0.9
 *
 * Library for communicating with
 * Epsolar/Epever Tracer A/BN MPPT Solar Charger Controllers
 *
 * THIS PROGRAM COMES WITH ABSOLUTELY NO WARRANTIES !
 * USE IT AT YOUR OWN RISKS !
 *
 * Core library thanks to Luca Soltoggio.
 *
 */

// Include composer libraries autoloaded...
require __DIR__ . '/vendor/autoload.php';

use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;

require_once 'epsolar/PhpEpsolarTracer.php';

// Load configuration
$conf = json_decode(file_get_contents('/data/options.json'));

// Create new MQTT client using configuration variables
$mqtt = new MqttClient($conf->mqttServer, $conf->mqttPort);
$connectionSettings = (new ConnectionSettings)
    ->setUsername($conf->mqttUsername)
    ->setPassword($conf->mqttPassword);
$loopCount = 1;

echo "Epever Tracer Poller for Home Assistant.";

while (true) {

    // Re-create HA topics on first loop count.
    if ($loopCount == 1) registerTopics();

    // Poll tracer every time we loop....
    pollTracer();

    $loopCount++;
    // Reset loop counter every 10 times, so we can re-create the HA topics (if HA restarted etc)...
    if ($loopCount >= 10) $loopCount = 1;

    sleep($conf->pollingInterval); // Polling interval defined in config.
}


function registerTopics()
{
    global $conf;
    $tracerTopics = new PhpEpsolarTracer($conf);

    // ignore for now, doesnt seem to work properly
    // Tracer System Info & Firmware Versions...
//    if ($tracerTopics->getInfoData()) {
//        for ($i = 0; $i < count($tracerTopics->infoData); $i++) {
//            registerHATopic(preg_replace('/\s+/', '_', strtolower($tracerTopics->infoKey[$i])), "", "solar-power");
//        }
//    }

    // System Rated Data (maximum output, amps, etc)...
    if ($tracerTopics->getRatedData()) {
        for ($i = 0; $i < count($tracerTopics->ratedData); $i++) {
            registerHATopic($tracerTopics->ratedKey[$i], $tracerTopics->ratedSym[$i], "solar-power");
        }
    }

    // RealTime Data
    if ($tracerTopics->getRealtimeData()) {
        for ($i = 0; $i < count($tracerTopics->realtimeData); $i++) {
            registerHATopic($tracerTopics->realtimeKey[$i], $tracerTopics->realtimeSym[$i], "solar-power");
        }
    }

    // Statistical Data
    if ($tracerTopics->getStatData()) {
        for ($i = 0; $i < count($tracerTopics->statData); $i++) {
            registerHATopic($tracerTopics->statKey[$i], $tracerTopics->statSym[$i], "solar-power");
        }
    }

    // Settings Data
    if ($tracerTopics->getSettingData()) {
        for ($i = 0; $i < count($tracerTopics->settingData); $i++) {
            registerHATopic($tracerTopics->settingKey[$i], $tracerTopics->settingSym[$i], "solar-power");
        }
    }

    // Coils Data
    if ($tracerTopics->getCoilData()) {
        for ($i = 0; $i < count($tracerTopics->coilData); $i++) {
            registerHATopic($tracerTopics->coilKey[$i], "", "solar-power");
        }
    }

    // Discrete Data
    if ($tracerTopics->getDiscreteData()) {
        for ($i = 0; $i < count($tracerTopics->discreteData); $i++) {
            registerHATopic($tracerTopics->discreteKey[$i], "", "solar-power");
        }
    }

}


function pollTracer()
{
    global $conf;
    $tracer = new PhpEpsolarTracer($conf);

//    if ($tracer->getInfoData()) {
//        for ($i = 0; $i < count($tracer->infoData); $i++) {
//            sendHAData(preg_replace('/\s+/', '_', strtolower($tracer->infoKey[$i])), $tracer->infoData[$i]);
//        }
//    } else {
//        print "Cannot get Info Data\n";
//    }

    if ($tracer->getRatedData()) {
        for ($i = 0; $i < count($tracer->ratedData); $i++) {
            sendHAData(preg_replace('/\s+/', '_', strtolower($tracer->ratedKey[$i])), $tracer->ratedData[$i]);
            //print str_pad($i, 2, '0', STR_PAD_LEFT)." ".$tracer->ratedKey[$i].": ".$tracer->ratedData[$i].$tracer->ratedSym[$i]."\n";
        }
    }

    if ($tracer->getRealtimeData()) {
        for ($i = 0; $i < count($tracer->realtimeData); $i++) {
            sendHAData(preg_replace('/\s+/', '_', strtolower($tracer->realtimeKey[$i])), $tracer->realtimeData[$i]);
            //print str_pad($i, 2, '0', STR_PAD_LEFT)." ".$tracer->realtimeKey[$i].": ".$tracer->realtimeData[$i].$tracer->realtimeSym[$i]."\n";
        }
    }

    if ($tracer->getStatData()) {
        for ($i = 0; $i < count($tracer->statData); $i++) {
            sendHAData(preg_replace('/\s+/', '_', strtolower($tracer->statKey[$i])), $tracer->statData[$i]);
            //print str_pad($i, 2, '0', STR_PAD_LEFT)." ".$tracer->statKey[$i].": ".$tracer->statData[$i].$tracer->statSym[$i]."\n";
        }
    }

    if ($tracer->getSettingData()) {
        for ($i = 0; $i < count($tracer->settingData); $i++) {
            sendHAData(preg_replace('/\s+/', '_', strtolower($tracer->settingKey[$i])), $tracer->settingData[$i]);
            //print str_pad($i, 2, '0', STR_PAD_LEFT)." ".$tracer->settingKey[$i].": ".$tracer->settingData[$i].$tracer->settingSym[$i]."\n";
        }
    }

    if ($tracer->getCoilData()) {
        for ($i = 0; $i < count($tracer->coilData); $i++) {
            sendHAData(preg_replace('/\s+/', '_', strtolower($tracer->coilKey[$i])), $tracer->coilData[$i]);
            //print str_pad($i, 2, '0', STR_PAD_LEFT)." ".$tracer->coilKey[$i].": ".$tracer->coilData[$i]."\n";
        }
    }

    if ($tracer->getDiscreteData()) {
        for ($i = 0; $i < count($tracer->discreteData); $i++) {
            sendHAData(preg_replace('/\s+/', '_', strtolower($tracer->discreteKey[$i])), $tracer->discreteData[$i]);
            // print str_pad($i, 2, '0', STR_PAD_LEFT)." ".$tracer->discreteKey[$i].": ".$tracer->discreteData[$i]."\n";
        }
    }
}


function registerHATopic($sensorName, $displayUnits, $haIcon = 'solar-power')
{
    global $conf, $mqtt, $connectionSettings;

    echo "Creating sensor: " . $sensorName . "\n";

    $uniqueId = $conf->mqttDevicename . "_" . preg_replace('/\s+/', '_', strtolower($sensorName));

    $mqtt->connect($connectionSettings);
    $mqtt->publish(
        $conf->mqttTopic . '/sensor/' . $uniqueId . '/config',
        json_encode([
            'name' => $sensorName,
            'unique_id' => $uniqueId,
            'unit_of_measurement' => $displayUnits,
            'device_class' => 'energy',
            'state_topic' => $conf->mqttTopic . '/sensor/' .  $uniqueId,
            'icon' => 'mdi:' . $haIcon,
            'state_class' => str_contains($sensorName, 'Total ') ? 'total_increasing' : null,
        ]),
        0
    );
}

function sendHAData($sensorName, $sensorValue)
{
    global $conf, $mqtt, $connectionSettings;

    if ($conf->verboseDebugging == true) {
        echo $sensorName . ": ";
        echo $sensorValue . "\n";
    }

    if (!empty($sensorValue)) {
        $name = $conf->mqttDevicename . "_" . $sensorName;

        $mqtt->connect($connectionSettings);
        $mqtt->publish(
            $conf->mqttTopic . '/sensor/' . $name,
            $sensorValue,
            0
        );
    }
}

