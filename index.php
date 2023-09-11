<?php
$example_persons_array = [
    [
        'fullname' => 'Иванов Иван Иванович',
        'job' => 'tester',
    ],
    [
        'fullname' => 'Степанова Наталья Степановна',
        'job' => 'frontend-developer',
    ],
    [
        'fullname' => 'Пащенко Владимир Александрович',
        'job' => 'analyst',
    ],
    [
        'fullname' => 'Громов Александр Иванович',
        'job' => 'fullstack-developer',
    ],
    [
        'fullname' => 'Славин Семён Сергеевич',
        'job' => 'analyst',
    ],
    [
        'fullname' => 'Цой Владимир Антонович',
        'job' => 'frontend-developer',
    ],
    [
        'fullname' => 'Быстрая Юлия Сергеевна',
        'job' => 'PR-manager',
    ],
    [
        'fullname' => 'Шматко Антонина Сергеевна',
        'job' => 'HR-manager',
    ],
    [
        'fullname' => 'аль-Хорезми Мухаммад ибн-Муса',
        'job' => 'analyst',
    ],
    [
        'fullname' => 'Бардо Жаклин Фёдоровна',
        'job' => 'android-developer',
    ],
    [
        'fullname' => 'Шварцнегер Арнольд Густавович',
        'job' => 'babysitter',
    ],
];

// ------------------------
// из строки ФИО возвращает массив ['surname' => 'Фамилия', 'name' => 'Имя', и 'patronomyc' => 'Отчечство']

function getPartsFromFullname ($fullname) {
    
    $keysName = ['surname', 'name', 'patronomyc'];
    $valuesName = explode(' ', $fullname, 3);
    $numOfElements = count($valuesName);

    for ($i = 1; $i < 4; $i++) {        // дополнить пустыми элементами при недостатке данных
        if ($numOfElements < $i) {
            $valuesName[] = ''; 
        };
    };

    $partsName = array_combine($keysName,$valuesName);

    return($partsName);
};

// ------------------------
// возвращает ФИО, склеенное из трех частей имени

function getFullnameFromParts ($surname, $name, $patronomyc) {

    $fullname = "$surname $name $patronomyc";

    return $fullname;
};

// ------------------------
// возвращает Фамилия И. из полного ФИО

function getShortName ($fullname) {

    $partsName = getPartsFromFullname($fullname);
    $shortName = $partsName['surname'] . ' '. mb_substr($partsName['name'] , 0 , 1) . '.';

    return $shortName;
};

// ------------------------
// определение пола по ФИО

function getGenderFromName ($fullname) {
    
    $partsName = getPartsFromFullname($fullname);                   // Получили массив с ФИО

    $endSurname = mb_substr($partsName['surname'] , -2 , 2);        // окончание Фамилии
    $endName = mb_substr($partsName['name'] , -1 , 1);              // окончание Имени
    $endPatronomyc =  mb_substr($partsName['patronomyc'] , -3 , 3); // окончание Отчества

    $flagGender = 0;    // неопределенный пол

    if ( $endPatronomyc == 'вна' ) {                    // отчество 
        $flagGender -= 1;                               
    } elseif (mb_substr($endPatronomyc , -2 , 2) === 'ич') {   
        $flagGender += 1;
    };

    if ( $endName  == 'а' ) {                           // имя 
        $flagGender -= 1;
    } elseif ($endName  == 'й' || $endName  == 'н') {     
        $flagGender += 1;
    };

    if ( $endSurname  == 'ва' ) {                       // фамилия
        $flagGender -= 1;
    } elseif (mb_substr($endSurname , -1 , 1) == 'в') {   
        $flagGender += 1;
    };
    
    $flagGender = $flagGender <=> 0;

    return $flagGender;
};

function onlyMan($person) {             // является ли человек мужчиной 
    return (getGenderFromName($person['fullname']) === 1) ;
};

function onlyWoman($person) {           // является ли человек женщиной 
    return (getGenderFromName($person['fullname']) === -1) ;
};

function percent($full, $part) {        // $full - 100 %, part - часть от full  c округлением до 0.1
    return round($part * 100 / $full, 1); 
};
 
// ------------------------
// определения полового состава аудитори

function getGenderDescription ($persons_array) {        
    
    $countPeople = count($persons_array);                               // всего людей
    $countMen = count(array_filter($persons_array, 'onlyMan'));         // мужчин
    $countWomen = count(array_filter($persons_array, 'onlyWoman'));     // женщин

    $men = percent($countPeople, $countMen);
    $women = percent($countPeople, $countWomen);
    $noGender = percent($countPeople, ($countPeople - $countMen - $countWomen));

    $genderComposition = <<<HEREDOCLETTER
    Гендерный состав аудитории:
    ---------------------------
    Мужчины - $men%
    Женщины - $women%
    Не удалось определить - $noGender% 
HEREDOCLETTER;

    return $genderComposition;
};

// ------------------------
// Идеальный подбор пары

function getPerfectPartner ($surname, $name, $patronomyc, $persons_array) {

    // Нормализация написания имен 

    $surname =  mb_convert_case($surname, MB_CASE_TITLE_SIMPLE);        
    $name =  mb_convert_case($name, MB_CASE_TITLE_SIMPLE);
    $patronomyc =  mb_convert_case($patronomyc, MB_CASE_TITLE_SIMPLE);

    // Склеим ФИО

    $fullName = getFullnameFromParts ($surname, $name, $patronomyc);

    // Определим пол

    $flagGender = getGenderFromName($fullName);

    // Отфильтруем противоположный пол

    switch ($flagGender) {
        case 1: 
            $arr = array_filter($persons_array, 'onlyWoman');     // массив из женщин
            break;
        case -1:
            $arr = array_filter($persons_array, 'onlyMan');       // массив из мужчин
            break;
        default:
            $message = "Ваш пол неопределен!\nИзвините, мы не смогли подобрать Вам пару...((";
            return $message;
    };

    $countArr = count($arr);                    // Кол-во людей в фильтрованном массиве

    if ($countArr === 0) {                  // в фильтрованном массиве нет нужного пола
        $message = "Извините, мы не смогли подобрать Вам пару...((";
        return $message;
    };

    // Ищем случайный элемент массива

    $randomCounterElement = rand(0, ($countArr - 1) );      
    print_r($randomCounterElement); echo "\n";
    $i = 0;                 // счетчик
    $shortNamePair = '';                        // краткое имя пары

    foreach ($arr as $person) {
        if ( $i == $randomCounterElement ) {     //дошли до нужного элемента
            $shortNamePair = getShortName ($person['fullname']); // краткое имя пары
        };
        $i += 1;
    };

    if ($shortNamePair === '') {                        // не нашли пару
        $message = "Извините, что-то пошло не так...((";
        return $message;
    };   

    $shortName = getShortName ($fullName);      // краткое имя "пациента"

    $randomPersent = round(rand(5000, 10000) / 100, 2);     // процент совместимости

    // Возврат фразы

    $message = <<<HEREDOCLETTER
    $shortName + $shortNamePair = 
    ♡ Идеально на $randomPersent% ♡
HEREDOCLETTER;

    return $message;
};


// ------------------------
// body - проерка функций
// ------------------------


$genderComposition = getGenderDescription($example_persons_array);
echo $genderComposition;

echo "\n\n";

print_r(getPerfectPartner('Иванов', 'Иван', 'Иванович', $example_persons_array));

echo "\n\n";

print_r(getPerfectPartner('ИваноВа', 'мАРИЯ', 'Ивановна', $example_persons_array));

?>
