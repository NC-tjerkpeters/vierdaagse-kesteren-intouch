# Vrijwilligersrooster

Module voor het beheren van vrijwilligers en het inplannen per avond en rol.

## Locatie

**Werkgroep → Vrijwilligersrooster**

## Functionaliteit

- **Lijst** – Overzicht van alle vrijwilligers (naam, e-mail, telefoon, aantal ingeplande shifts)
- **Rooster** – Matrix: dagen × rollen. Per cel kies je welke vrijwilliger ingepland is.
- **Rollen** – Scanner, Post, Start, Finish, Overig (aanpasbaar in `config/volunteers.php`)

## Permissies

| Permissie | Omschrijving |
|-----------|--------------|
| `vrijwilligers_view` | Rooster en lijst bekijken |
| `vrijwilligers_manage` | Vrijwilligers toevoegen/bewerken/verwijderen, rooster invullen |

## Rollen aanpassen

Bewerk `config/volunteers.php` om rollen toe te voegen of te wijzigen. De sleutels (scanner, post, etc.) worden in de database opgeslagen.
