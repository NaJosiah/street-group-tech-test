# Homeowner Name Parser

A Laravel console application that parses homeowner name strings from CSV data into structured person records. Handles single names, initials, hyphenated surnames, and couples.

## Project Structure

| File | Description |
|------|-------------|
| `app/Services/HomeOwnerParser.php` | Core parsing logic — splits name strings into structured Person objects |
| `app/DataTransferObjects/Person.php` | Immutable DTO with `title`, `first_name`, `initial`, `last_name` |
| `app/Console/Commands/ParseHomeOwners.php` | Artisan command that reads the CSV and outputs parsed JSON |
| `storage/app/private/examples.csv` | Sample input data |
| `tests/Unit/HomeOwnerParserTest.php` | Unit tests for the parser |

## Usage

The CSV file is located at `storage/app/private/examples.csv`. Run the parser with:

```bash
php artisan app:parse-home-owners
```

This outputs each person as structured JSON with `title`, `first_name`, `initial`, and `last_name` fields.

## Tests

```bash
php artisan test
```
