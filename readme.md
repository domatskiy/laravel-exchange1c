# Exchange with 1C

```
composer require domatskiy/laravel-exchange1c
```
 
add Exchange1CServiceProvider to the providers section of /config/app.php
```php
Domatskiy\Exchange1C\Exchange1CServiceProvider::class
```
 
### publish
```
php artisan vendor:publish --provider="Domatskiy\Exchange1C\Exchange1CServiceProvider"
```
 
### events
```php
'Domatskiy\Exchange1C\Events\ImportComplate' => [
    'App\Listeners\ImportComplate',
    ],
```
 
 
ImportComplate prop
```
[type] => catalog
[dir] => /var/www/.../storage/app/1c_exchange
[file] => import0_1.xml
```
        

example: class App\Listeners\ImportComplate
```php
use Illuminate\Support\Facades\Event;

class ImportComplate
{
    /**
     * Handle the event.
     *
     * @param  Event  $event
     * @return void
     */
    public function handle(Event $event)
    {
        \Log::info('event: '. print_r($event, true));
    }
}
```
 
 