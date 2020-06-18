<?php


$p = JSON::load("php://input");

$where = [$mySQL->parse(
  "created BETWEEN {int} AND {int}",
  $p['period'][0],
  $p['period'][1]
)];
if (isset($p['SellerID'])) {
  $where[] = $mySQL->parse(
    "SellerID = {int}",
    $p['SellerID']
  );
}
if (isset($p['MaxPrice'])) {
  $where[] = $mySQL->parse(
    "Price BETWEEN {int} AND {int}",
    ($p['MinPrice'] ?? 1),
    $p['MaxPrice']
  );
}

$rows = $mySQL->get(
"  SELECT
    CatID,
    name AS Category,
    COUNT(cb_store.ThingID) AS cnt
  FROM
    cb_things
  JOIN 
    cb_store USING(ThingID)
  JOIN
    cb_categories ON cb_categories.CatID = cb_store.CategoryID
  WHERE
    {prp}
  GROUP BY CategoryID
",
  implode(" AND ", $where)
);

$total = 0;
foreach ($rows as $row) {
  $total += (INT)$row['cnt'];
}
if(isset($p['endfile'])):?>
<h1><?=$p['endfile']?></h1>
<?else:?>
<h2>Found <?=$total?> positions in <?=count($rows)?> categories:</h2>
<fieldset><legend>Categories</legend>
  <div class="columns-2">
  <?foreach($rows as $row):?>
    <label><input type="checkbox" name="category" value="<?=$row['CatID']?>" checked> <?=json_decode($row['Category'], true)['de']?> (<?=$row['cnt']?>)</label>
  <?endforeach?>
  </div>
</fieldset>
<br>
<div align="right">
  <label class="left">File Name: <input type="text" name="endfile" value="<?=date("d-M_H")?>" size="15" required><b>.xlsx</b></label>
  Header: <input type="text" name="header" value="<?=date("d F, Y H:i")?>">
</div>
<fieldset><legend>Fields</legend>
  <div class="columns-3">
    <label><input type="checkbox" name="field" value="cb_items.ItemID AS ID"> InnerID</label>
    <label><input type="checkbox" name="field" value="ReferenceID"> eBayID</label>
    <label><input type="checkbox" name="field" value="DEID AS CategoryID"> CategoryID</label>
    <label><input type="checkbox" name="field" value="NameDE AS Category" checked> Category</label>
    <label><input type="checkbox" name="field" value="images" checked> Image</label>
    <label><input type="checkbox" name="field" value="Named" checked> Named</label>
    <label><input type="checkbox" name="field" value="State AS `Condition`"> Condition</label>
    <label><input type="checkbox" name="field" value="Price" checked> Price</label>
    <label><input type="checkbox" name="field" value="created AS Date"> Date</label>
    <label><input type="checkbox" name="field" value="CONCAT('<?=$config->{BASE_FOLDER}?>/',cb_categories.slug,'-',cb_items.ItemID) AS Backlink" checked> Back link</label>
  </div>
</fieldset>
<br>
<?endif;
