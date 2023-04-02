https://github.com/joedixon/laravel-translation

$language = Language::where('language', $request->lang)->first();
        if($language){
            $translations =  array_reduce(array_column(json_decode($language->translations, true), 'group_key'), 'array_merge', array());
            session()->put('locale', $request->lang);
            session()->put('translations', $translations);
        }
        return redirect()->back();
        
        
        
// Middelware


<?php
  
namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Support\Facades\App;

class LanguageManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $lang = config('app.locale');
        
        if (session()->has('locale')) {
            $lang = session()->get('locale');
            App::setLocale(session()->get('locale'));
        }

        if(session()->has('translations')){
            app('translator')->addLines(session()->get('translations') , $lang);
        }else{
            $language = Language::where('language', $lang)->first();
            if($language){
                $translations =  array_reduce(array_column(json_decode($language->translations, true), 'group_key'), 'array_merge', array());
                session()->put('translations', $translations);
                app('translator')->addLines($translations , $lang);
            }
        }
          
        return $next($request);
    }
}

// Models -----------------------

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Language extends Model
{
    use HasFactory;

    public function translations(){
        return $this->hasMany(Translation::class, 'language_id', 'id');
    }

}









<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    protected $attributes = ['group_key'];

    protected $appends = ['group_key'];

    public function getGroupKeyAttribute(){
        $attributes = $this->getAttributes();
        return [$attributes['group'].'.'.$attributes['key'] => $attributes['value']];
    }
}
