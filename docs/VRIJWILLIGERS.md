# Vrijwilligersrooster

Module voor het beheren van vrijwilligers en het inplannen per avond en rol.

## Locatie

**Werkgroep → Vrijwilligersrooster**

## Functionaliteit

- **Lijst** – Overzicht van alle vrijwilligers (naam, e-mail, telefoon, beschikbaarheid, aantal ingeplande shifts)
- **Beschikbaarheid** – Per vrijwilliger kun je aangeven op welke avonden hij/zij beschikbaar is
- **Bevoegd verkeersregelaar** – Checkbox bij vrijwilliger: mag verkeer regelen langs de route (alleen deze kunnen als verkeersregelaar worden ingepland)
- **Rooster** – Matrix: dagen × rollen. Per cel kies je welke vrijwilliger ingepland is. Bij het kiezen zie je "(niet beschikbaar)" als iemand die dag niet kan
- **Rollen** – Scanner, Post, Start, Finish, Overig (aanpasbaar in `config/volunteers.php`)
- **Verkeersregelaars** – Aparte tab: verkeersregelaars worden per route ingepland (niet in het dag-rooster). Alleen vrijwilligers die bevoegd zijn én beschikbaar op de dag(en) van de route worden getoond. Iemand kan niet tegelijk in het rooster én verkeersregelaar op dezelfde dag staan – dit wordt geblokkeerd en gemeld (⛔). Dubbel gepland op dezelfde dag krijgt een waarschuwing (⚠️).

## Permissies

| Permissie | Omschrijving |
|-----------|--------------|
| `vrijwilligers_view` | Rooster en lijst bekijken |
| `vrijwilligers_manage` | Vrijwilligers toevoegen/bewerken/verwijderen, rooster invullen |

## Rollen aanpassen

Bewerk `config/volunteers.php` om rollen toe te voegen of te wijzigen. De sleutels (scanner, post, etc.) worden in de database opgeslagen.
