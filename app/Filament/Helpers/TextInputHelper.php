<?php

namespace App\Filament\Helpers;

class TextInputHelper
{
    public static function string(int $maxLength = 255): array
    {
        $onkeydown = "return true;";

        $oninput = "let max={$maxLength};let wrp=this.closest('.fi-input-wrp');let el=this.closest('.fi-fo-field')||this.parentElement;let err=el.querySelector('.fi-fo-invalid');if(this.value.length>=max){this.value=this.value.slice(0,max);if(wrp)wrp.style.boxShadow='0 0 0 2px #EF4444';if(!err){err=document.createElement('p');err.className='fi-fo-invalid';err.style.cssText='color:#EF4444;font-size:0.875rem;margin-top:0.25rem;';el.appendChild(err);}err.textContent='Karakter maksimal berjumlah '+max;}else{if(wrp)wrp.style.boxShadow='';if(err)err.remove();}";

        return compact('onkeydown', 'oninput');
    }
}
