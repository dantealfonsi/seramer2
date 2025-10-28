<?php

class RolesController {
    /**
     * Verifica si el usuario tiene permiso para una determinada acción.
     * * @param string $area Ejemplo: 'INFRACTIONS', 'CONFIG_RATES', 'USERS_AUDIT'.
     * @param string $type Ejemplo: 'r' (read), 'w' (write), 'x' (execute).
     * @return bool True si tiene permiso, false en caso contrario.
     */
    function hasPermission(string $area, string $type): bool {
        // 1. Obtener la máscara de la sesión
        $mask = $_SESSION['user_permissions_mask'] ?? '---------'; 
        
        // 2. Determinar la posición del carácter de permiso
        $position = -1;
        switch (strtoupper($area)) {
            case 'INFRACTIONS':
                $offset = 0; // r:0, w:1, x:2
                break;
            case 'CONFIG_RATES':
                $offset = 3; // r:3, w:4, x:5
                break;
            case 'USERS_AUDIT':
                $offset = 6; // r:6, w:7, x:8
                break;
            default:
                return false; // Área no definida
        }

        switch (strtolower($type)) {
            case 'r': $position = $offset + 0; break;
            case 'w': $position = $offset + 1; break;
            case 'x': $position = $offset + 2; break;
            default: return false;
        }
        
        // 3. Verificar el carácter en la máscara
        // Si la máscara es 'rwx-rwx-rwx' y buscamos INFRACTIONS ('r'):
        // substr(mask, 0, 1) -> 'r'. Es igual al tipo buscado ('r'). -> TRUE
        $permission_char = substr($mask, $position, 1);

        return ($permission_char === strtolower($type));
    }

}