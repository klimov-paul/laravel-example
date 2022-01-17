Active Record Listings
======================

Most of the time a common web application returns data from the server, e.g. responding to the 'GET' HTTP method.
The most common response is a "listing". Unfortunately, these are rare a simple list of records - they usually include
complex filtering, sorting and pagination.

You do not need a "repository" to get list of records. Remember that it is advised to always use `Eloquent::query()` method
for the selection queries. It returns a `Builder` instance, which is already holds many helper methods for the listing composition.
Do not be afraid to work around the `Builder` instance.

Obviously, we can allow direct operation of the `Builder` instance by client-side requests. Otherwise, we will create a
security breach and catch an SQL injection.
Data should be verified and sanitized before being used in query composition.

To avoid code duplication for the listings you can use a separated library like [spatie/laravel-query-builder](https://github.com/spatie/laravel-query-builder).
Open [App\Http\Controllers\Api\BookController](../app/Http/Controllers/Api/BookController.php) see `index()`.

Usually each listing has a single-time usage. It is unlikely to be reused anywhere else. For example: listing of "books"
at for users will be different from the listing for administrators.

Still, you can wrap listing logic into separated class to simplify controller code.

Open [App\Http\Controllers\Api\Me\FavoriteController](../app/Http/Controllers/Api/Me/FavoriteController.php) see `index()`.
Pay attention how we get "favorites" as the relation of authenticated user.


JSON Resources
--------------

JSON resource classes are used to create a representation ("View") layer for API application.
Always define a JSON resource or collection for the API endpoints.

See [App\Http\Resources\FavoriteResource](../app/Http/Resources/FavoriteResource.php).
Pay attention that some resource can use another for the nested models.
