<?php
namespace BankAPI;

use mysqli;
use Exception;

class TransfersController {
    
    public static function handleRequest(mysqli $db) {
        $request = new TransfersRequest();
        $token = $request->getToken();

        if (!Token::check($token, $_SERVER['REMOTE_ADDR'], $db)) {
            $response = new TransfersResponse([]);
            $response->sendError('Invalid token');
            return;
        }

        try {
            $userId = Token::getUserId($token, $db);
        } catch (Exception $e) {
            $response = new TransfersResponse([]);
            $response->sendError('Invalid token');
            return;
        }

        $accountNo = Account::getAccountNo($userId, $db);

        $sql = "SELECT * FROM transfer WHERE source = ? OR target = ?";
        $query = $db->prepare($sql);
        $query->bind_param('ii', $accountNo, $accountNo);
        $query->execute();
        $result = $query->get_result();
        
        $transfers = [];
        while ($row = $result->fetch_assoc()) {
            $transfers[] = [
                'source' => $row['source'],
                'target' => $row['target'],
                'amount' => $row['amount']
            ];
        }

        $response = new TransfersResponse($transfers);
        $response->send();
    }
}
?>
