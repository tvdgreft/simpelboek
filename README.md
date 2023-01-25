# SIMPELBOEK
## wat het wel en niet kan
Simpelboek is een eenvoudig boekhoudprogramma voor verenigingen en stichtingen.
Simpelboek bevat de volgende functionaliteit:
* Beheren van een rekeningschema
* Begroting maken
* Handmatig invoeen van boekingen
* Automatisch verwerken van boeken van ING en Triodos
* Maken en exporteren van balans, resultatenrekening en grootboek
* Verwerken van BTW en maken van BTW overzichten.

Wat Simpelboek niet kan:
* beheer debiteuren en crediteuren
* beheer facturen

## hoe installeren
Simpelboek kan op wordpress worden geinstalleerd als een plugin. 
Deze plugin installeren gaat als volgt:
Ga naar https://github.com/tvdgreft/simpelboek
klik op de knop Code en vervolgens op Download ZIP.
Bestand opslaan op pc
In wordpress website inloggen als administrator.
Ga naar: plugins - nieuwe plugin
plugin uploaden. Bestand kiezen die net is opgeslagen vanuit github.
Tenslotte de plugin activeren.
Vervolgens een pagina aanmaken met de volgende tekst: [simpelboek].  
Deze pagina aan een menu hangen en start de pagina.  
De plugin is getest met GeneralPress als thema. 
Bij instellingen - permalink moet bij Algemene instellingen Berichtnaam zijn aangeklikt.

Je kunt meerdere boekhoudingen aanmaken. Wil je dat iemand maar met 1 boekhouding kan werken, maak dan de volgende pagina aan:  
[simpelboek single=code]  
code is de boekhoudcode die je hebt aangemaakt.  
Ook deze pagima linken met een menu en iemand kan dan alleen op die boekhouding werken.  

## instellingen

Nadat de plugin is geinstalleerd kunnen er een aantal Instellingen worden aangemaakt.
klik op Instellingen -> Simpelboek.
Hier kan de bedrijfsnaam, de achtergrondkleur worden ingegeven.
De bestandsnaam bij rekeningschema is een voorbeeld van een meegeleverd rekeningschema.
Deze kan worden ingeladen bij het invoeren van een rekeningschema. (menuoptie rekeningen)
Het bestand staat in de map data.
Het is mogelijk een afwijkend voorbeeldbestand aan te maken in dezelfde map onder een andere naam.
Deze naam moet dan wel worden ingevulkd bij: 'bestand rekeningschema'

## database conversie oudere versies
Om een boekhouding die in een oudere versie is aangemaakt naar deze versie over te zetten moeten de volgende stappen worden ondernomen:
Maak een nieuwe boekhouding aan.
Verwijder de volgende tabellen: {prefix}_sbh_*_{code}  {code} = databankcode
Download de volgende tabellen in een sql bestand:
{prefix}_simpelboek_{code}_*        {prefix} = database perefix {code} = naam van de boekhouding.   * kan alles zijn
Wijzig de bestandsnamen als volgt:
{prefix}_simpelboek_{code}_rekeningen wordt {prefix nieuwe database }_sbh_rekeningen_{code}
{prefix}_simpelboek_{code}_begroting wordt {prefix nieuwe database }_sbh_begroting_{code}
{prefix}_simpelboek_{code}_balans wordt {prefix nieuwe database }_sbh_balans_{code}
{prefix}_simpelboek_{code}_boeking wordt {prefix nieuwe database }_sbh_boekingen_{code}

Voeg een veld modified in (datetime) na id in tabel {prefix nieuwe database }_sbh_boekingen_{code}
