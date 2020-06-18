<?php

$product = $mySQL->getRow("
    SELECT
        ThingID,
        CategoryID,
        preview,
        named,
        selling,
        BrandID
    FROM
        cb_store
    WHERE
        ThingID={int}
    LIMIT 1",

    ARG_2
);

$named = JSON::parse($product['named']);

$marks = $mySQL->get("SELECT BrandID, brand, slug FROM cb_brands ORDER BY idx");

$handle = "b".time();
?>
<div id="<?=$handle?>" class="mount" style="width:540px">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
    .box-body{
        font-size: 0;
    }
    .box-body > img{
        width: 100%;
        height: 250px;
        object-fit: cover;
    }
    .box-body > textarea{
        width: 98%;
        margin: 5px 1%;
        resize: none;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #AAA;
        box-sizing: border-box;
        font: bold 18px calibri;
        background-image: linear-gradient(to top, #FFF, #EEE);
        box-shadow: inset 0 0 8px -2px #00000050;
    }
    .box-body > fieldset{
        font-size: 15px;
        float:right;
        padding: 0 5px;
        border-width: 0;
    }
    .box-body > fieldset > input{
        font: bold 18px calibri;
    }
    .box-body > fieldset > input,
    .box-body > fieldset > select{
        height: 30px;
        padding: 6px;
        border-radius: 5px;
        border: 1px solid #AAA;
        box-sizing: border-box;
        background-image: linear-gradient(to top, #FFF, #EEE);
        box-shadow: inset 0 0 8px -2px #00000050;
    }
    .box-body > .caption{
        color: #08B;
        margin: 40px 0 0 10px;
        font: bold 20px calibri;
    }
    </style>
    <form class="box discount-box white-bg" autocomplete="off">
        <button type="reset" class="close-btn white-txt" title="close" data-translate="title">✕</button>
        <div class="box-caption active-bg">&#xe90b;<?include_once("components/movebox.php")?></div>
        <div class="h-bar dark-btn-bg" data-translate="textContent">Product Box</div>
        <div class="box-body light-btn-bg" align="right">
            <img src="<?=$product['preview']?>" alt="" width="100%">
            <input name="en" type="hidden" value="<?=$named['en']?>">
            <textarea name="de" rows="3"><?=$named['de']?></textarea>
            <fieldset>
                <legend>Mark: </legend>
                <select name="mark">
                    <?if(empty($product['BrandID'])):?>
                    <option selected disabled>Select Mark</option>
                    <?endif?>
                    <?foreach($marks as $mark):?>
                    <option value="<?=$mark['BrandID']?>" <?if($mark['BrandID']==$product['BrandID']):?>selected<?endif?> ><?=$mark['brand']?></option>
                    <?endforeach?>
                    <option value="0">Other</option>
                </select>
            </fieldset>
            <fieldset>
                <legend>Price:</legend>
                <input type="text" name="price" value="<?=$product['selling']?>" placeholder="€" size="7">
            </fieldset>
            <span class="caption left">Dictionary:</span>
            <table rules="cols" width="100%" cellpadding="5" cellspacing="0" bordercolor="#CCC">
                <thead>
                    <tr class="light-btn-bg">
                        <th width="28"></th>
                        <th data-translate="textContent">Keyword</th>
                        <th width="120" data-translate="textContent">Translate</th>
                        <th width="28"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
                        <td contenteditable="true"></td>
                        <td contenteditable="true"></td>
                        <th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="box-footer light-btn-bg" align="right">
            <button type="submit" class="light-btn-bg" data-translate="textContent">save</button>
            <button name="delete" class="dark-btn-bg" data-translate="textContent">remove</button>
        </div>
        <script>
        (function(form){
            var feed = document.querySelector("#tile");
            form.onreset=function(event){form.drop()}
            form.delete.onclick=function(event){
                event.preventDefault();
                XHR.push({
                    addressee: "/ebay-client/actions/rm_itm/<?=$product['ThingID']?>",
                    onsuccess: function(response){
                        feed.removeChild(feed.querySelector("a.snippet[data-id='<?=$product['ThingID']?>']"));
                        parseInt(response) ? form.drop() : alertBox(response);
                    }
                });
            }
            form.onsubmit=function(event){
                event.preventDefault();
                XHR.push({
                    addressee: "/ebay-client/actions/sv_itm/<?=$product['ThingID']?>",
                    body: JSON.encode({
                        de: form.de.value.trim(),
                        en: form.en.value.trim(),
                        Price: form.price.value.trim(),
                        BrandID: form.mark.value,
                        CatID: <?=$product['CategoryID']?>,
                        dictionary: (function(dictionary, key){
                            form.querySelectorAll("table>tbody>tr>td").forEach(function(cell,i){
                                if(i%2 && key){
                                    dictionary[key] = cell.textContent.trim();
                                }else if(cell.textContent.length) {
                                    key = cell.textContent.trim();
                                }
                            });
                            return dictionary;
                        })({})
                    }),
                    onsuccess: function(response){
                        form.drop();
                        location.reload();
                    }
                });
            }
        })(document.currentScript.parentNode);
        </script>
    </form>
    <script>
    (function(mount){
        location.hash = "<?=$handle?>";
        translate.fragment(mount);
        if(mount.offsetHeight>(screen.height - 40)){
            mount.style.top = "20px";
        }else mount.style.top = "calc(50% - "+(mount.offsetHeight/2)+"px)";
    })(document.currentScript.parentNode);
    </script>
</div>