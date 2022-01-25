Database Migrations
===================

Each database migration file represent a **complete logical** DB structure state change. If you know the books should be
grouped by categories, there is no need to split the change-set into separated migrations. They should be united instead.
See [CreateBooks](../database/migrations/2022_01_05_154705_create_books.php) migration for example.

Defining "many-to-many" relation use the following notation for the junction (pivot) table name: "{table1}_has_{table2}".
See "book_has_category" table definition at [CreateBooks](../database/migrations/2022_01_05_154705_create_books.php) migration for example.


Indexes
-------

Always evaluate the tables you create for the indexes setup. Such fields like 'status', 'group', nullable timestamp flags (like 'banned_at')
should always be indexed.

**Heads up!** Remember that, unlike MySQL, PostgreSQL does NOT automatically create indexes along with the foreign keys.
You should manually define indexes for the junction fields (like 'category_id') in case you use PostgreSQL.


Foreign Keys
------------

There are three options for the foreign key constraint:

 - restrict
 - cascade
 - set null

Particular option should be chosen depending on the actual logic.

When it comes to payments and money processing, all related data should always remain in the database for the accounting and bookkeeping.
If it comes to proceedings about financial fraud, these data becomes crucial. Thus, for the tables related to finance
`resctrict` constraint should always be used. Once payment record inserted all related data about credit card and user
should become forbidden for deletion.
See [CreatePayments](../database/migrations/2022_01_06_172155_create_payments.php) migration for example.

For the non-crucial data, like content management, user's wish list and so on, `cascade` or `set null` constraint option should be used.
So if we delete particular record from "categories" table, related records at junction table "book_has_category" should be deleted,
ensuring our database does not contain garbage records.

**Heads up!** Be careful setting up `cascade` constraint option, so you do not cause main record deletion on junction table
record deletion.
