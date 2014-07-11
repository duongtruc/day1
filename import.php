<?php
$conn = mysql_connect("127.0.0.1", "root", "1234") or die("Can't connect database!");
mysql_select_db("rubikin_db", $conn);
$delSql = "TRUNCATE TABLE product;TRUNCATE TABLE category;TRUNCATE TABLE `option`;TRUNCATE TABLE option_value;TRUNCATE TABLE product_category;TRUNCATE TABLE product_option;";
mysql_query($delSql);
mysql_query("ALTER TABLE  `category` ADD INDEX  `cate_name_index` (`name`);");
$file = fopen("products.csv", "r") or die("can't open file!");
$row = fgetcsv($file, 0, ",");
foreach ($row as $head)
{
$headArr = explode("_", $head);
if (strcmp($headArr[0], "option") == 0)
{
$r = mysql_query("select * from `option` where name = '".$headArr[1]."');");
if (mysql_num_rows($r) == 0)
{
$query = "insert into `option`(name) values('".$headArr[1]."');";
mysql_query($query);
}
}
}
while (!feof($file))
{
$sql = "insert into product(name, slug, short_description, description, available_on, created_at, updated_at, deleted_at, variant_selection_method) values";
$line =  fgetcsv($file);
for ($i = 5; $i <= 8; $i++)
if ($line[$i] != '')
{
$date = DateTime::createFromFormat('n/d/Y H:i', $line[$i]);
$line[$i] = $date->format('Y-m-d H-i-s');
}
$sql .= "('$line[1]', '$line[2]', '$line[3]', '$line[4]', '$line[5]', '$line[6]', '$line[7]', '$line[8]', '$line[9]');" ;
mysql_query($sql);
$currentIdResult = mysql_fetch_array(mysql_query("select MAX(id) from product;"));
$maxID = $currentIdResult[0];
$cateArr = explode(";", $line[12]);
foreach ($cateArr as $cate)
{
$sql = "select id from category where `name` = '".$cate."' limit 1;";
$result = mysql_query($sql);
$exist = mysql_fetch_array($result);
if ($exist[0] == 0)
mysql_query("Insert into category(`name`) values('".$cate."');");
$sql = "insert into product_category(product_id, category_id) values(".$maxID.", (select MAX(id) from category))";
mysql_query($sql);
}
$colorArr = explode(";", $line[10]);
foreach ($colorArr as $color) {
$reQ = mysql_query("select id from `option` where name = 'color' limit 1;");
$resu = mysql_fetch_array($reQ);
$colorId = $resu[0];
$sql = "insert into option_value(option_id, `value`) values(".$colorId.",'".$color."');";
mysql_query($sql);
$sql ="insert into product_option(product_id, `option_value_id`) values(".$maxID.", (select MAX(id) from option_value));";
mysql_query($sql);
}
$sizeArr = explode(";", $line[11]);
foreach ($sizeArr as $size) {
$reQ = mysql_query("select id from `option` where name = 'size' limit 1;");
$resu = mysql_fetch_array($reQ);
$sizeId = $resu[0];
//echo "color id = ".$colorId;
$sql = "insert into option_value(option_id, `value`) values(".$sizeId.",'".$size."');";
//echo $q;
mysql_query($sql);
$sql ="insert into product_option(product_id, `option_value_id`) values(".$maxID.", (select MAX(id) from option_value));";
mysql_query($sql);
}
}
