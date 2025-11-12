<?php
    function validateRememberCookie($user_id, $mysqli) {
        if (!is_numeric($user_id)) {
            return false;
        }
        
        $stmt = $mysqli->prepare("SELECT id, login, first_name, last_name, administrator, foto FROM user WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($id, $login, $first_name, $last_name, $administrator, $foto);
        
        if ($stmt->fetch()) {
            $user_data = [
                'id' => $id,
                'login' => $login,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'administrator' => $administrator,
                'foto' => $foto
            ];
            $stmt->close();
            return $user_data;
        }
        
        $stmt->close();
        return false;
    }

    function createRememberToken($user_id) {
        return $user_id;
    }
?>