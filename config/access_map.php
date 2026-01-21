<?php
// Configuración de Control de Acceso por Departamento (Contexto)
// Define qué departamentos tienen acceso a qué rutas (carpetas o archivos específicos)

// NOTA: Si una ruta no está definida aquí, es accesible por defecto (o se puede configurar para denegar por defecto)
// Se recomienda denegar por defecto si se busca máxima seguridad, pero para este parche usaremos "Deny List" o "Specific Allow List".

return [
    // === RECURSOS HUMANOS ===
    'views/staff' => ['Recursos Humanos'],
    'views/attendance' => ['Recursos Humanos'],
    'views/leave-requests' => ['Recursos Humanos'],
    'views/vacations' => ['Recursos Humanos'],
    'views/departments' => ['Recursos Humanos'],
    'views/reports/hr.php' => ['Recursos Humanos'],
    'views/users/index.php' => ['Recursos Humanos', 'Fiscalizacion'], // Jefes de Fisc también ven usuarios? Según menú, tienen "users-fisc"

    // === FISCALIZACION ===
    'views/inspections' => ['Fiscalizacion'],
    'views/inspectors' => ['Fiscalizacion'],
    'views/infractions' => ['Fiscalizacion'],
    'views/infractions-type' => ['Fiscalizacion'],
    'views/sanctions' => ['Fiscalizacion'],         
    'views/sanctionsType' => ['Fiscalizacion'],
    'views/citations' => ['Fiscalizacion'],
    'views/users-fisc' => ['Fiscalizacion'],
    
    // === LIQUIDACION ===
    'views/awardees' => ['Liquidacion'],
    'views/market_stalls' => ['Liquidacion'],
    'views/zones' => ['Liquidacion'],
    'views/sectors' => ['Liquidacion'],
    'views/internal_categories' => ['Liquidacion'],
    'views/external_categories' => ['Liquidacion'],
    'views/rates' => ['Liquidacion'],
    'views/reports/liquidacion.php' => ['Liquidacion'],
    'views/reports/billing.php' => ['Liquidacion'], // Según menú
    
    // === COBRANZA ===
    'views/billing' => ['Cobranza'],
    'views/daily_cash' => ['Cobranza'],
    'views/cash_registers' => ['Cobranza'],
    'views/collection-reports' => ['Cobranza'],
    
    // === COMPARTIDOS ===
    'views/complaints' => ['Fiscalizacion', 'Recursos Humanos'], // Ambos tienen acceso en el menú
    'views/contracts' => ['Recursos Humanos', 'Liquidacion'],    // Ambos tienen acceso
    'views/reports/index.php' => ['Fiscalizacion'], 
    
    // Rutas protegidas genéricas
    'views/users' => ['Recursos Humanos'], 
    'views/profile' => ['Recursos Humanos', 'Fiscalizacion', 'Liquidacion', 'Cobranza'], // Acceso universal al perfil
];
