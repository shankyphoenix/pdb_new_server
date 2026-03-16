<?php
namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller; 
use App\Http\Resources\FilterResource;  
use Illuminate\Support\Str;

class FilterController extends Controller
{
    protected $filters = [];
    protected $sys;
    protected $enableCache;
    protected $cachedFilterPath;

    public function  __construct() {
       // $this->enableCache = true;
        //$this->cachedFilterPath = storage_path('app/'.Str::slug(str_replace("/", " ", request()->route()->uri),"_").'.json');
    }
       public function serviceType()
    {
        $filter = [
            $this->filter('lead', __('Lead'), 'services'),
            $this->filter('pos', __('POS'), 'services'),
            $this->filter('quote', __('Quote'), 'services'),
        ];

        $this->filters[] = $this->filter(null, __('Service Type'), 'services', $filter);

        return $this;
    }

    public function cachedResponse() {        

        // If file already exists, return its contents
        if (file_exists($this->cachedFilterPath)) {
            return response()->json(
                json_decode(file_get_contents($this->cachedFilterPath), true)
            );
        }

        return false;
    }

    public function filters()
    {
        $this->serviceType();

        $response = FilterResource::collection($this->filters);
        
        if($this->enableCache) {
            file_put_contents($this->cachedFilterPath, $response->toJson(JSON_PRETTY_PRINT));
        }

       return $response;
    }   

    public function filter($key, $name, $type, $sub = [], $inputType = null, $filtertype = null)
    {
        return new class($key, $name, $type, $sub, $inputType, $filtertype)
        {
            public function __construct($key, $name, $type, $sub, $inputType, $filtertype)
            {
                $this->id   = $key;
                $this->name = $name;
                $this->type = $type;
                $this->inputType  = $inputType;
                $this->sub  = $sub;
                $this->filtertype  = $filtertype;
            }
        };
    }
}
