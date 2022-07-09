<?php



function get_type_of_documents()

{

    $documents = [

        1 => "CI",

        2 => "Pasaporte",

        3 => "Licencia de conducir",

    ];

    

    return $documents;

}



function get_marital_statuses()

{

    $m_statuses = [

        "married" => "Casado/a",

        "widow" => "Viudo/a",

        "single" => "Soltero/a"

    ];

    

    return $m_statuses;

}



function get_occupations()

{

    $occupations = [

        1 => "Profesionales BPO",

        2 => "Policía/Ejército",

        3 => "Abogado/Procurador/Notario",

        4 => "Trabajador Profesional (Médicos, Ingenieros, Contadores, etc.)",

        5 => "Servicios financieros",

        6 => "Empleado del gobierno",

        7 => "Trabajador minorista",

        8 => "Propietario de empresa",

        9 => "Empleado de empresa privada",

        10 => "Independiente",

        11 => "Trabaja por cuenta propia",

        12 => "Estudiante",

        13 => "Desempleado",

        14 => "Retirado",

        15 => "Otro",

    ];

    

    return $occupations;

}



function get_work_terms()

{

    $work_terms = [

        1 => "menos de 3 meses",

        2 => "más de 3 meses pero menos de 1 año",

        3 => "1-3 años",

        4 => "4-10 años",

        5 => "más de 10 años",

    ];

    

    return $work_terms;

}

