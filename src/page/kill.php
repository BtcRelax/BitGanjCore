<?php
namespace BtcRelax;

\BtcRelax\SecureSession::killSession();
\BtcRelax\Utils::redirect("main");
