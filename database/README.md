# Database files

Database structure and demo content are intentionally separated.

## Fresh installation

Create the target database first:

```sh
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS gamegear_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
```

Apply the versioned schema migration to that database:

```sh
mysql -u root -p gamegear_hub < database/migrations/001_create_schema.sql
```

Then load the optional demo seed:

```sh
mysql -u root -p gamegear_hub < database/seeds/001_demo_data.sql
```

The migration and seed do not hardcode a database name; the MySQL command selects the target database. Replace `gamegear_hub` in these commands when using a different `DB_NAME`.

The migration creates an empty schema with no customer, administrator, category, or product records. The seed adds the demo administrator, catalog categories, and products and is intended to run once on a fresh schema.
