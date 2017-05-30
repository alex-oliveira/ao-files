# Ao-Files

### 1) Installing
````
"require": {
    "alex-oliveira/ao-files": "1.0.*"
},
````
````
"repositories": [{
    "type": "vcs",
    "url": "https://bitbucket.org/alex-oliveira/ao-files.git"
}]
````

or
````
$ composer require alex-oliveira/ao-files
````

### 2) Configuring "config/app.php" file
````
'providers' => [
    /*
     * Vendor Service Providers...
     */
    AoFiles\ServiceProvider::class,
],
````

### 3) Configuring migration
````
public function up()
{
    Schema::create('category_file', function (Blueprint $table) {
        $table->integer('category_id')->unsigned();
        $table->foreign('category_id')->references('id')->on('categories');
        
        $table->bigInteger('file_id')->unsigned();
        $table->foreign('file_id')->references('id')->on('ao_files_files');
        
        $table->primary(['category_id', 'file_id']);
    });
}

public function down()
{
    Schema::drop('category_file');
}
````

### 4) Configuring model
````
namespace App\Models;

use AoFiles\Models\File;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * @return File[]|\Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function files()
    {
        return $this->belongsToMany(File::class);
    }
}
````

### 5) Creating controller class
````
namespace App\Controllers\Categories;

use AoFiles\Controllers\AoFilesController;
use App\Models\Category;

class FilesController extends AoFilesController
{

    protected $dynamicClass = Category::class;

    protected $subFolders = ['categories' => 'category_id'];

}
````

### 6) Configuring routes
````
Route::group(['prefix' => 'categories', 'as' => 'categories.'], function () {
    
    AoFiles()->router()->controller('Categories\FilesController')->foreign('category_id')->make();
    
    Route::get('/',                 ['as' => 'index',       'uses' => 'CategoriesController@index']);
    Route::get('/{id}',             ['as' => 'show',        'uses' => 'CategoriesController@show']);
    Route::post('/',                ['as' => 'store',       'uses' => 'CategoriesController@store']);
    Route::put('/{id}',             ['as' => 'update',      'uses' => 'CategoriesController@update']););
    Route::delete('/{id}',          ['as' => 'destroy',     'uses' => 'CategoriesController@destroy']);
    
});
````

### 7) Cheking routes
````
$ php artisan route:list
````