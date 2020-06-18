<?php

namespace App\Http\Controllers;

use App\ItemModel;
use App\BrandModel;
use App\LineupModel;
use App\StoreModel;
use App\CategoryModel;
use App\Storekeeper\Storekeeper;
use App\SMRegenerator\Regenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\eBay\Client;
use App\eBay\Tools\Parser;

use App\Office\Excel;

class Assistant extends Controller
{
    private $timestamp = null;

    private $dictionary = [];
    private $categories = [];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($method)
    {
        $this->timestamp = time();
        return $this->{$method}();
    }

    public function siteMapRegenerator()
    {
        $map = new \SimpleXMLElement("../../www/public/sitemap.tpl.xml", 0, true);
        Regenerator::refreshMainMap($map);
        $map->asXML("../../www/public/sitemap.xml");
    }
    public function itemsMapRegenerator()
    {
        $map = new \SimpleXMLElement("../../www/public/sitemap.tpl.xml", 0, true);
        Regenerator::refreshItemsMap($map);
        $map->asXML("../../www/public/itemsmap.xml");
    }
    public function modelsMapRegenerator()
    {
        $map = new \SimpleXMLElement("../../www/public/sitemap.tpl.xml", 0, true);
        Regenerator::refreshModelsMap($map);
        $map->asXML("../../www/public/modelsmap.xml");
    }
    public function motorsMapRegenerator()
    {
        $map = new \SimpleXMLElement("../../www/public/sitemap.tpl.xml", 0, true);
        Regenerator::refreshMotorsMap($map);
        $map->asXML("../../www/public/motorsmap.xml");
    }

    private function otoba()
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);

        $tree = \JSON::load('./reports/tree.json');

        foreach ($tree as $mark => $motors) {
            
            print $mark."<br>";
            print "_______________<br>";
            $brand = BrandModel::where('brand', $mark)->first();

            foreach ($motors as $article => $link) {
                
                print $article."<br>";

                $dom->loadHTMLFile($link);
                libxml_clear_errors();

                $xpath = new \DOMXpath($dom);

                $MotorID = Str::slug($article);
                $specifics = $this->specifics($xpath);
                $compatibility = $this->compatibility($xpath);
                $images = $this->images($xpath, dirname($link));

                $motor = [
                    'motor_id' => $MotorID,
                    'article' => $article,
                    'published' => 1,
                    'specifications' => \JSON::stringify($specifics)
                ];
                

                if (count($images) > 0) {
                    
                    $path = pathinfo($images[0]);

                    $image = file_get_contents($images[0]);
                    //if (!file_exists("../../www/public/img/motors/{$brand->slug}")) {
                    @mkdir("../../www/public/img/motors/{$brand->slug}");
                    //}
                    $saved = file_put_contents("../../www/public/img/motors/{$brand->slug}/{$MotorID}.{$path['extension']}", $image);
                    if ($saved) {
                        $motor['picture'] = "/img/motors/{$brand->slug}/{$MotorID}.{$path['extension']}";
                    }
                }

                

                if (DB::table('cb_motors')->where('motor_id', $MotorID)->exists()) {
                    
                    //$motor['compatibility'] = \JSON::stringify($compatibility);

                    DB::table('cb_motors')
                        ->where('motor_id', $MotorID)
                        ->update($motor);

                    //print "UPDATE: ".$mark." ".$article."<br>";
                } else {
                    $motor['compatibility'] = \JSON::stringify($compatibility);
                    DB::table('cb_motors')->insert($motor);

                    //print "INSERT: ".$mark." ".$article."<br>";
                }

                if (
                    DB::table('motors_vs_brands')
                    ->where('BrandID', $brand->BrandID)
                    ->where('motor_id', $MotorID)
                    ->exists()
                ) {} else {
                    DB::table('motors_vs_brands')
                    ->insert([
                        'motor_id' => $MotorID,
                        'BrandID' => $brand->BrandID
                    ]);
                }

                $lineups = LineupModel::where('BrandID', $brand->BrandID)->get();

                foreach ($compatibility as $model) {
                    
                    //print $label."<br>";
    
                    $lineup = Parser::checkLineup($model, $lineups);

                    if (isset($lineup[0])) {
                        $LineID = $lineup[0];
                        if (
                            DB::table('motors_vs_lineups')
                            ->where('line_id', $LineID)
                            ->where('motor_id', $MotorID)
                            ->exists()
                        ) {} else {
                            DB::table('motors_vs_lineups')
                            ->insert([
                                'line_id' => $LineID,
                                'motor_id' => $MotorID
                            ]);
                        }
                    }
                }
            }
        }
    }

    private function motorlist()
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTMLFile("https://otoba.ru/dvigatel/catalog.html");
        libxml_clear_errors();

        $xpath = new \DOMXpath($dom);
        $links = $xpath->query('//div[@class="auto-model"]/a');

        $tree = [];
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            $brand = pathinfo($href)['filename'];
            $tree[$brand] = "https://otoba.ru/dvigatel/{$href}";
        }
        sleep(1);

        foreach ($tree as $brand => &$link) {
            $dom->loadHTMLFile($link);
            libxml_clear_errors();

            $xpath = new \DOMXpath($dom);
            $links = $xpath->query('//a[@class="rubr-s"]');
            $link = [];
            foreach ($links as $motorlink) {
                $href = $motorlink->getAttribute('href');
                $article = trim($motorlink->nodeValue);

                $link[$article] = "https://otoba.ru/dvigatel/{$href}";
            }
        }
        \JSON::save("./reports/tree.json", $tree);
    }
    private function images($xpath, $path)
    {
        $images = [];
        $pictures = $xpath->query('//div[@class="post-img"]/figure/picture/img');
        foreach ($pictures as $picture) {
            $images[] = $path."/".$picture->getAttribute("src");
        }
        return $images;
    }
    private function compatibility($xpath)
    {
        $compatibility = [];
        $tables = $xpath->query('//table[@class="tab-prim"]');
        foreach ($tables as $table) {
            $barnd = trim($table->getElementsByTagName('caption')[0]->nodeValue);
            
            $lineup = null;
            $cells = $table->getElementsByTagName('td');
            foreach ($cells as $i => $cell) {
                if ($i%2 && $lineup) {
                    $years = trim($cell->nodeValue);
                    $compatibility[] = "{$barnd} {$lineup} ".preg_replace("/- н.в./i", "On", $years);
                } else {
                    $lineup = trim($cell->nodeValue);
                    if (strlen($lineup) < 3) {
                        $lineup = null;
                    }
                }
            }
        }
        return $compatibility;
    }
    private function specifics($xpath)
    {
        $key = null;
        $specifics = [];
        $dictionary = [
            'keys' => [
                'Система питания' => "fuel injection",
                'Мощность двс' => "power",
                'Тип топлива' => "fuel type",
                'Крутящий момент' => "torque",
                'Блок цилиндров' => "cylinder block alloy",
                'Точный объем'=>"engine displacement",
                'Головка блока' => "cylinder head",
                'Диаметр цилиндра' => "cylinder diameter",
                'Ход поршня' => "piston stroke",
                'Степень сжатия' => "compression ratio",
                'Особенности двс' => "features",
                'Гидрокомпенсаторы' => "hydraulic compensators",
                'Привод ГРМ' => "timing drive",
                'Фазорегулятор' => "variable valve",
                'Турбонаддув' => "turbocharger",
                'Экологический класс' => "emissions standard",
                'Примерный ресурс' => "lifespan",
            ],
            'values' => [
                'fuel type' => [
                    'дизель' => "disel",
                    'бензин' => 'petrol',
                    'АИ-92' => "petrol",
                    'АИ-95' => "petrol",
                    'АИ-98' => "petrol",
                ],
                'fuel injection' => [
                    'комбинир. впрыск' => "combined fuel injection",
                    'комбин. впрыск' => "combined fuel injection",
                    'комбинированный' => "combined fuel injection",
                    'вихрекамера' => "swirl chamber",
                    'Вихрекамера' => "swirl chamber",
                    'насос-форсунки' => "pump injection",
                    'карбюратор' => "carburetor",
                    'форкамера' => "prechamber",
                    'форкамеры' => "prechamber",
                    'инжектор' => "injector",
                    'прямой впрыск' => "direct injection",
                    'Прямой впрыск' => "direct injection",
                    'моновпрыск' => "single-point",
                    'распр. впрыск' => "multipoint fuel injection",
                    'распределенный впрыск' => "multipoint fuel injection",
                    'многоточечный впрыск' => "multipoint fuel injection",
                    'карб.' => "carburetor",
                    'или' => "or"
                ],
                'torque' => [
                    'Нм' => "Nm"
                ],
                'power' => [
                    'л.с.' => "hp"
                ],
                'cylinder block alloy' => [
                    'чугунный' => "cast-iron",
                    'алюминиевый' => "aluminium"
                ],
                'engine displacement' => [
                    'см³' => "cm³"
                ],
                'cylinder head' => [
                    'чугунная'  => "cast-iron",
                    'алюминиевая' => "aluminium",
                    'или' => "or"
                ],
                'cylinder diameter' => [
                    'мм' => "mm"
                ],
                'piston stroke' => [
                    'мм' => "mm"
                ],
                'compression ratio' => [],
                'features' => [
                    'V-VIS до 1996 года' => "V-VIS until 1996",
                    'VSR опция' => "VSR optional",
                    'балансиры' => "balancers",
                    'механический ТНВД' => "mechanical HPFP",
                    'электронный ТНВД' => "electronic HPFP",
                    'с 2004 года' => "since 2004",
                    'электронный дроссель' => "electronic choke",
                    'интеркулер опция' => "intercooler",
                    'балансирные валы' => "twin balance shafts",
                    'система hpi' => "hpi system",
                    'после 2011 г.' => "after 2011",
                    'интеркулер' => "intercooler",
                    'Твинпорт'  => "twinport",
                    'нет' => "no",
                    'и' => "&"
                ],
                'hydraulic compensators' => [
                    'на впуске' => "intake",
                    'на выпуске' => "exhaust",
                    'только на ГБЦ' => "cylinder head 12v only",
                    'после 1984 года' => "after 1984",
                    'до 1994 года' => "until 1994",
                    'до 1999 года' => "until 1999",
                    'до 2005 года' => "until 2005",
                    'да с 2004 года' => "since 2004",
                    'да' => "yes",
                    'нет' => "no",
                    'опция' => "yes",
                ],
                'timing drive' => [
                    'цепь Морзе' => "Morse chain",
                    'цепь\/шестерни' => "chain & gears",
                    'цепь и шестерни' => "chain & gears",
                    'зубчатый ремень' => "toothed belt",
                    '4 цепи' => "four chains",
                    'четыре цепи' => "four chains",
                    'ремень \+ цепи' => "belt and chains",
                    'ремень и две цепи' => "belt and two chains",
                    'ремень и пара цепей' => "belt and two chains",
                    'ремень и цепи' => "chain & belt",
                    'ремень плюс цепь' => "chain & belt",
                    'три цепи' => "three chains",
                    'две цепи' => "two chains",
                    'пара цепей' => "two chains",
                    'однорядная цепь' => "single row chain",
                    'двухрядная цепь' => "double row chain",
                    'цепь двухрядная' => "double row chain",
                    'шестерни' => "gears",
                    'шестеренчатый' => "gears",
                    'ремень' => "belt",
                    'ременной' => "belt",
                    'цепь' => "chain",
                    'цепной' => "chain",
                    'два ремня' => "two belts"
                ],
                'variable valve' => [
                    'с 2013 года' => "since 2013",
                    'на всех валах' => "on all shafts",
                    'гнц' => "chain tensioners",
                    'на обоих валах' => "on both shafts",
                    'на впуске с 1999 года' => "intake since 1999",
                    'на впуске и выпуске' => "inlet and exhaust",
                    'на впускном валу' => "on the intake shaft",
                    'на впускных валах' => "on the intake shafts",
                    'опция' => "NVCS",
                    'только на впуске' => "intake",
                    'на впуске' => "intake",
                    'на выпуске' => "exhaust",
                    'упр. натяжитель' => "controlled tensioner",
                    'л.с.' => "hp",
                    'на' => "on",
                    'нет' => "no",
                    'да' => "yes"
                ],
                'turbocharger' => [
                    'опция AVCS' => "AVCS optional",
                    'одна TF035HL8' => "turbine",
                    'обычная и VGT' => "VGT",
                    'обычная и VNT' => "VNT",
                    'турбина KKK' => "turbine KKK",
                    'турбина ККК' => "turbine KKK",
                    'нагнетатель' => "supercharger",
                    'одна турбина Garrett' => "Garrett",
                    'одна или две турбины' => "single or twin turbo",
                    'одна или две' => "single or twin turbo",
                    'одна Garrett T3' => "Garrett T3",
                    'одна турбина' => "single turbo",
                    'две турбины' => "twin turbo",
                    'с интеркулером' => "with intercooler",
                    'компрессор' => "compressor",
                    'турбина' => "turbine",
                    'двойной' => "twin turbo",
                    'две' => "twin turbo",
                    'или' => "or",
                    'нет' => "no",
                    'да' => "yes"
                ],
                'lifespan' => [
                    'км' => "Km"
                ],
                'emissions standard' => [
                    'ЕВРО' => "EURO",
                    'нет' => "no"
                ]
            ]
        ];
        $cells = $xpath->query('//table[@class="tab-tth"]/tr/td');

        $cyrillic = false;
        foreach ($cells as $i => $cell) {
            if ($i%2 && $key) {
                $v = trim($cell->nodeValue);

                $specifics[$key] = preg_replace(array_map(function($key){
                    return "/({$key})/i";
                }, array_keys($dictionary['values'][$key])), array_values($dictionary['values'][$key]), $v);

                if (preg_match("/[а-я]{3,}/i", $specifics[$key], $matches)){
                    dd($key, $v, $specifics[$key], $matches);
                    $cyrillic = true;
                }
            } else {
                $k = trim($cell->nodeValue);
                $key = (isset($dictionary['keys'][$k])) ? $dictionary['keys'][$k] : null;
                if (preg_match("/[а-я]{2,}/i", $key)){
                    $cyrillic = true;
                }
            }
        }

        if ($cyrillic) {
            dd($specifics);
        }

        return $specifics;
    }    
    private function explorer()
    {
        // 193363813812
        // 193363858680
        // 193363862647
        // 193364569729

        // 193363813812
        // 193363862647

        //$response = Client::FetchToken(0);
        //$response = Client::GetSessionID(0);
        //$response = Client::GetAccount(0);
        //$response = Client::GetItem(0);
        $response = Client::EndFixedPriceItem(77);
        //$response = Client::GetSellerList(77);
        //$response = Client::AddFixedPriceItem(77);
        //$response = Client::GeteBayDetails(77);

        dd($response);

    }
    public static function checkMotor()
    {   
        $things = ItemModel::where('category_id', 225)
            ->where('status', 'available')
            ->get();

        foreach ($things as $i => $thing) {
            $motors = DB::table('motors_vs_lineups')
                ->join('cb_motors', 'motors_vs_lineups.motor_id', '=', 'cb_motors.motor_id')
                ->select('cb_motors.motor_id','article')
                ->where('line_id', $thing->line_id)
                ->get();

            $MotorID = null;
            if ($motors->count() > 0) {

                $MotorID = Parser::checkMotor(
                    (\JSON::parse($thing->named)['de'])."  ".(\JSON::parse($thing->options)['Part Number'] ?? ""),
                    $motors
                );

                if (empty($MotorID)) {
                    print "{$i}. No Motor: {$str}<br>";
                } else {
                    DB::table('cb_store')
                        ->where('ThingID', $thing->id)
                        ->update([
                            'motor_id' => $MotorID
                        ]);
                }
            }
        }
    }
    private function parser()
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTMLFile("https://www.drom.ru/catalog/audi/engine/");
        libxml_clear_errors();

        $codes = [];
        $articles = [];
        foreach($dom->getElementsByTagName("h4") as $node){
            if ($node->getAttribute('class') == "b-title_b-title_type_h4") {
                $lst = $node->nextSibling;
                while($lst && ($lst->nodeType != 1)) {
                    $lst = $lst->nextSibling;
                }
                $links = $lst->getElementsByTagName("a");
                foreach ($links as $link) {
                    $items = preg_split("/\/|,\s*|;\s*/", $link->nodeValue);
                    if (count($items) > 1) {
                        foreach ($items as $item) {
                            $codes[$item] = $link->getAttribute("href");
                        }
                    } elseif(empty($articles[$link->nodeValue])) {
                        $articles[$link->nodeValue] = $link->getAttribute("href");
                    }
                }
            }
        }
        foreach ($codes as $code => $href) {
            if(empty($articles[$code])) {
                $articles['-'.$code] = $href;
            }
        }
        //foreach ($articles as &$article) {
        //    $article = array_unique($article);
        //}
        print "<pre>";
        print_r($articles);
        print "</pre><hr>";
        //foreach ($codes as &$code) {
        //    $code = array_unique($code);
        //}

        //print "<pre>";
        //print_r($codes);
        //print "</pre>";
    }
    private function autoplay()
    {
        $storekeeper = new Storekeeper();
        $section = (date("H") / 3)>>0;
        $log = [];
        foreach ($storekeeper->taskmap[$section] as $task) {
            $affected = $storekeeper->{$task['name']}($task['tag']);
            $log[] = "Task: {$task['desc']} - Affected: {$affected}";
            if ($affected) {
                $storekeeper->createTask($task['desc'], $task['tag'], $affected);
                break;
            }
        }
        print implode("<br>", $log);
    }

    private function report()
    {
        $log = [];
        $timeoffset = (time() - (3600 * 24));
        $table = ItemModel::where('created_at', '>', $timeoffset)
            ->orderBy('category_id')
            ->get();
        $newItems = $table->count();
        $log[] = "New Items: {$newItems}";

        $completedItems = ItemModel::where('status', "deleted")
            ->where('updated_at', '>', $timeoffset)
            ->count();
        $log[] = "Completed Items: {$completedItems}";

        $orders = DB::table('cb_orders')
            ->join('cb_community', function($join){
                $join->on('cb_community.CommunityID','cb_orders.CommunityID');
            })
            ->where('created_at', '>', $timeoffset)
            ->get();

        Storekeeper::buildOrders($orders);

        $path = $this->exportToExcel($table);

        \Mail::send('mails.report', [
            'newItems'    => $newItems,
            'completedItems'  => $completedItems,
            'orders'  => $orders
        ], function($message) use ($path){
            //$message->to('datamorg@gmail.com')
            $message->to('info@motordock.de')
                ->subject("Motordock reports");
            $message->from('bestellung@motordock.de','Motordock Site');
            $message->attach($path, []);
        });

        print implode("<br>", $log);
    }

    private function exportToExcel($table = [])
    {
        $titles = ["Seller","Make","Named","Price","Image","Link"];
        $lastcol = range("A","Z")[count($titles)-1];

        $sheet = new Excel($lastcol);

        // Header
        $sheet->mergeCells("A1:{$lastcol}1");
        $sheet->setStyle("A1", [
            "font"=>[
                "color"=>["rgb" => "000000"],
                "bold" => true,
                "size" => 20
            ],
            "alignment" => ["horizontal" => "center"]
        ]);
        $sheet->fillCell("A1", date("d F, Y"));
        // Column Titles
        $sheet->colTitles($titles, "FFFFFF", "00ADF0");
        $sheet->setStyle("A2:{$lastcol}2", [
            "font"=>[
                "color"=>["rgb" => "FFFFFF"],
                "bold" => true,
                "size" => 11
            ],
            "fill"=>[
                "fillType" => "solid",
                "startColor" => ["rgb" => "00ADF0"]
            ],
            "alignment" => ["horizontal" => "center"]
        ]);
        // Fill Sheet
        $y = 2;
        $category = "";
        foreach ($table as &$row) {
            $y++;
            $row->slug = "https://motordock.de/{$row->category->slug}-{$row->id}";
            $row->named = json_decode($row->named, true)['de'] ?? "";
            $row->images = json_decode($row->images, true)[0] ?? "";
            if ($category != $row->category_id) {
                $category = $row->category_id;
                $sheet->mergeCells("A{$y}:{$lastcol}{$y}");
                $sheet->setStyle("A{$y}:{$lastcol}{$y}", [
                    "font"=>[
                        "color"=>["rgb" => "FFFFFF"],
                        "bold" => true,
                        "size" => 15
                    ],
                    "fill"=>[
                        "fillType" => "solid",
                        "startColor" => ["rgb" => "111111"]
                    ],
                    "alignment" => ["horizontal" => "center"]
                ]);
                $sheet->fillCell("A{$y}", $row->category->name);
                $y++;
            }
            $sheet->fillRow([
                $row->seller->alias,
                $row->brand->brand ?? "",
                $row->named." - ".$row->id,
                $row->selling,
                $row->images,
                $row->slug
            ], $y);
            if ($y % 2) {
                $sheet->setStyle("A{$y}:{$lastcol}{$y}", [
                    "fill"=>[
                        "fillType"   => "solid",
                        "startColor" => ["rgb"=>"EEEEEE"]
                    ]
                ]);
            }
        }
        // Set Border
        $sheet->setStyle("A2:{$lastcol}{$y}", [
            "borders" => [
                "outline" => ["borderStyle" => "thick"]
            ]
        ]);
        // Save Sheet
        $path = "./reports/".date("Y-m-d").".xlsx";
        $sheet->saveSheet($path);
        return $path;
    }
}
