<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php';

function sendSupplyRequestEmail($name, $department, $items, $product_ids, $quantities, $units)
{
    $department = strtoupper($department);

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'stlaf.it01@gmail.com';
        $mail->Password   = 'ydzx yaii jqif pzza';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->Timeout    = 10; // fail fast if the network blocks/drops outbound SMTP, rather than hanging

        $mail->setFrom('stlaf.it01@gmail.com', 'Supply Request Notice');
        $mail->addAddress('stlaf.purchasing@gmail.com');

        $mail->isHTML(true);
        $mail->Subject = 'New Supply Request';


        $itemsArr       = explode(", ", $items);
        $productIdsArr  = explode(", ", $product_ids);
        $quantitiesArr  = explode(", ", $quantities);
        $unitsArr       = explode(", ", $units);

        $tableRows = '';
        for ($i = 0; $i < count($itemsArr); $i++) {
            $tableRows .= "<tr style='text-align:center;'>
                <td style='padding:5px;border:1px solid #ccc;'>{$itemsArr[$i]}</td>
                <td style='padding:5px;border:1px solid #ccc;'>{$productIdsArr[$i]}</td>
                <td style='padding:5px;border:1px solid #ccc;'>{$quantitiesArr[$i]}</td>
                <td style='padding:5px;border:1px solid #ccc;'>{$unitsArr[$i]}</td>
            </tr>";
        }

        // Email body
        $mail->Body = "
            <h3>Supply Request Submitted</h3>
            <p><strong>Name:</strong> {$name}</p>
            <p><strong>Department:</strong> {$department}</p>
            <table style='border-collapse:collapse;width:100%;margin-top:10px;'>
                <thead>
                    <tr style='background-color:#f2f2f2;text-align:center;'>
                        <th style='padding:8px;border:1px solid #ccc;'>Item</th>
                        <th style='padding:8px;border:1px solid #ccc;'>Product ID</th>
                        <th style='padding:8px;border:1px solid #ccc;'>Quantity</th>
                        <th style='padding:8px;border:1px solid #ccc;'>Unit</th>
                    </tr>
                </thead>
                <tbody>
                    {$tableRows}
                </tbody>
            </table>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}