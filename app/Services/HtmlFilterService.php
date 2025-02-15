<?php

namespace App\Services;

use DOMDocument;
use Illuminate\Database\Eloquent\Collection;

class HtmlFilterService
{
    public function filterHtml($html)
    {
        $doc = new DOMDocument();
        $doc->loadHTML($html);

        $script_tags = $doc->getElementsByTagName('script');

        foreach($script_tags as $script_tag){
            $script_tag->parentNode->removeChild($script_tag);
        }

        return $doc->saveHTML();
    }
}



?>