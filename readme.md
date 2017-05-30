# Ao-Files

### 1) Installing
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

### 3) Publish migrations
````
$ php artisan vendor:publish
$ composer dump
````





# Utilization 

## Migration

### Up
````
public function up()
{
    AoFiles()->schema()->create('posts');
}
````
the same that
````
public function up()
{    
    Schema::create('ao_files_x_posts', function (Blueprint $table) {
        $table->integer('post_id')->unsigned();
        $table->foreign('post_id', 'fk_posts_x_ao_files')->references('id')->on('posts');
        
        $table->bigInteger('file_id')->unsigned();
        $table->foreign('file_id', 'fk_ao_files_x_posts')->references('id')->on('ao_files_files');
        
        $table->primary(['post_id', 'file_id'], 'pk_ao_files_x_posts');
    });
}
````

### Down
````
public function down()
{
    AoFiles()->schema()->drop('posts');
}
````
the same that
````
public function down()
{    
    Schema::dropIfExists('ao_files_x_posts');
}
````





## Model
````
namespace App\Models;

use AoFiles\Models\File;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{

    /**
     * @return File[]|\Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function files()
    {
        return $this->belongsToMany(File::class, AoFiles()->schema()->table($this->getTable()));
    }
    
}
````
the same that
````
return $this->belongsToMany(File::class, 'ao_files_x_posts');
````





## Controller
````
namespace App\Http\Controllers\Posts;

use AoFiles\Controllers\AoFilesController;
use App\Models\Post;

class FilesController extends AoFilesController
{

    protected $dynamicClass = Post::class;
    
    protected $subFolders = ['posts' => 'post_id'];
    
}
````





## Routes
````
Route::group(['prefix' => 'posts', 'as' => 'posts.'], function () {

    AoFiles()->router()->controller('Posts\FilesController')->foreign('post_id')->make();
    .
    .
    .
    
});
````

### Checking routes
````
$ php artisan route:list
````