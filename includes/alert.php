<?php
function set_alert(string $type, string $message): void {
    $_SESSION['alert'] = ['type' => $type, 'message' => $message];
}

function show_alert(): void {
    if (!empty($_SESSION['alert'])) {
        $type = htmlspecialchars($_SESSION['alert']['type']);
        $message = htmlspecialchars($_SESSION['alert']['message']);
        echo "<div class=\"alert alert-$type\">$message</div>";
        unset($_SESSION['alert']);
    }
}