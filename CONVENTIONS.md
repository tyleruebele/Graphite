Graphite Conventions
====

Database Conventions
---
- All row id columns, numeric keys, named *_id
- All dates stored as unix timestamps, named *_uts
- All model tables have primary key as first column
- All model tables have second column for last update date, stored as auto-updating database timestamp, called `updated_dts`
- All model tables have third column for created date, stored as unix timestamp, called `created_uts`
