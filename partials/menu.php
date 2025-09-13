<?php

    $htmlMenu = <<<'EOT'
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-users me-2"></i>Musikverein Denkendorf
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-list me-1"></i>Mitglieder
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Listen
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                            <li><a class="dropdown-item" href="birthday_list.php?year=%CURRENT_YEAR%" target="_blank">Geburtstage</a></li>
                            <li><a class="dropdown-item" href="anniversary_list.php?year=%CURRENT_YEAR%" target="_blank">Jubiläen</a></li>
                            <li><a class="dropdown-item" href="#" target="_blank">Beiträge</a></li>
                            <li><a class="dropdown-item" href="#" target="_blank">SEPA-Datei</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="fee_classes.php">
                            <i class="fas fa-tags me-1"></i>Beitragsklassen
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="member_fees.php">
                            <i class="fas fa-link me-1"></i>Zuordnungen
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled" href="events.php">
                            <i class="fas fa-calendar me-1"></i>Veranstaltungen
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logs.php">
                            <i class="fas fa-file-text me-1"></i>Logs
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    EOT;
    $htmlMenu = str_replace('%CURRENT_YEAR%', date('Y'), $htmlMenu);
    return $htmlMenu;
?>