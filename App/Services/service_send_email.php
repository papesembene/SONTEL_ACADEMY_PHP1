<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require_once __DIR__ . '/../../vendor/autoload.php';


function envoyer_email($email, $nom_complet, $matricule, $password, $promotion_nom, $referentiel_nom, $date_debut) {
    $mail = new PHPMailer(true);

    try {
        // Paramètres serveur SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Serveur SMTP de Gmail
        $mail->SMTPAuth = true;
        $mail->Username = 'sembenpape4@gmail.com'; // Votre adresse Gmail
        $mail->Password = 'gxmw nsuy ujmy npwf '; // Mot de passe d'application Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
        $mail->Port = 465;

        // Expéditeur
        $mail->setFrom('sembenpape4@gmail.com', 'GESTION APPRENANTS');
        // Destinataire
        $mail->addAddress($email, $nom_complet);

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = 'Bienvenue sur notre plateforme';
        $mail->Body = "
            <h2>Bienvenue $nom_complet</h2>
            <p>Vous avez été ajouté(e) à la promotion <strong>$promotion_nom</strong> dans le référentiel <strong>$referentiel_nom</strong>.</p>
            <p>Informations :</p>
            <ul>
                <li>Matricule : $matricule</li>
                <li>Email : $email</li>
                <li>Mot de passe temporaire : $password</li>
            </ul>
            <p>Veuillez changer votre mot de passe lors de votre première connexion.</p>
        ";

        $mail->send();
        error_log("Email envoyé avec succès à $email");
        return true;
    } catch (Exception $e) {
        error_log("Erreur d'envoi d'email : {$mail->ErrorInfo}");
        return false;
    }
}
