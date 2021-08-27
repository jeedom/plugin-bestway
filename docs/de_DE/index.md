# Bestway Plugin

Plugin zur Steuerung des Bestway Lazy Spa Milan über Jeedom.

Es verwaltet automatisch die Filtration und ihre Dauer entsprechend der Wassertemperatur


## Aufbau 

Geben Sie einfach Ihre Bestway-Anmeldeinformationen ein

## Automatische Filterung

Wenn Sie das Kontrollkästchen Automatische Filterverwaltung aktivieren, berechnet das Plugin zu Beginn jeder Stunde selbst die erforderliche Filterzeit gemäß der durchschnittlichen Wassertemperatur der vorherigen Stunde (basierend auf der Formel Filterzeit in Stunden = Wassertemperatur / 2)