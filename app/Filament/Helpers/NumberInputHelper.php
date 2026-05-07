<?php

namespace App\Filament\Helpers;

class NumberInputHelper
{
    public static function integer(?int $maxValue = null): array
    {
        $onkeydown = "return !['-','e','E','+','.',','].includes(event.key)";

        if ($maxValue !== null) {
            $oninput = "let prev=this.dataset.p||'';let v=this.value.replace(/\\D/g,'');if(v.length>1){v=v.replace(/^0+/,'')||'0';}let num=parseInt(v)||0;let mv={$maxValue};let ex=num>mv;if(ex){v=String(mv);}let msg='Nilai maksimal adalah '+mv.toLocaleString('id-ID');";
        } else {
            $oninput = "let prev=this.dataset.p||'';let v=this.value.replace(/\\D/g,'');if(v.length>1){v=v.replace(/^0+/,'')||'0';}let mv='9'.repeat(10);let ex=v.length>mv.length||(v.length==mv.length&&v>mv);if(ex){v=v.slice(0,mv.length);}let msg='Ini adalah batas digit maksimal';";
        }

        $oninput .= "if(v.length)v=v.replace(/\\B(?=(\\d{3})+(?!\\d))/g,'.');this.dataset.p=v;this.value=v;let wrp=this.closest('.fi-input-wrp');let el=this.closest('.fi-fo-field')||this.parentElement;let err=el.querySelector('.fi-fo-invalid');if(ex){if(wrp)wrp.style.boxShadow='0 0 0 2px #EF4444';if(!err){err=document.createElement('p');err.className='fi-fo-invalid';err.style.cssText='color:#EF4444;font-size:0.875rem;margin-top:0.25rem;';el.appendChild(err);}err.textContent=msg;}else{if(wrp)wrp.style.boxShadow='';if(err)err.remove();}";

        return compact('onkeydown', 'oninput');
    }

    public static function decimal(int $maxInt = 10): array
    {
        return [
            'onkeydown' => "return !['-','e','E','+'].includes(event.key)",
            'oninput' => "let prev=this.dataset.p||'';let v=this.value.replace(/[^0-9,]/g,'');if(v.startsWith(','))v=v.slice(1);let fc=v.indexOf(',');if(fc!==-1){v=v.slice(0,fc+1)+v.slice(fc+1).replace(/,/g,'');}let p=v.split(',');let i=p[0];let wrp=this.closest('.fi-input-wrp');let el=this.closest('.fi-fo-field')||this.parentElement;let err=el.querySelector('.fi-fo-invalid');if(p.length>1&&p[1].length>2){this.value=prev;if(wrp)wrp.style.boxShadow='0 0 0 2px #EF4444';if(!err){err=document.createElement('p');err.className='fi-fo-invalid';err.style.cssText='color:#EF4444;font-size:0.875rem;margin-top:0.25rem;';el.appendChild(err);}err.textContent='Maksimal 2 angka di belakang koma';}else{let d=p.length>1?p[1].slice(0,2):'';if(i.length>1){i=i.replace(/^0+/,'')||'0';}let mv='9'.repeat({$maxInt});let ex=i.length>mv.length||(i.length==mv.length&&i>mv);if(ex){i=i.slice(0,mv.length);}i=i.replace(/\\B(?=(\\d{3})+(?!\\d))/g,'.');this.dataset.p=p.length>1?i+','+d:i;this.value=this.dataset.p;if(ex){if(wrp)wrp.style.boxShadow='0 0 0 2px #EF4444';if(!err){err=document.createElement('p');err.className='fi-fo-invalid';err.style.cssText='color:#EF4444;font-size:0.875rem;margin-top:0.25rem;';el.appendChild(err);}err.textContent='Ini adalah batas digit maksimal';}else{if(wrp)wrp.style.boxShadow='';if(err)err.remove();}}",
        ];
    }
}
