<?php
$local = @file_get_contents('/etc/localdomains');
$remote = @file_get_contents('/etc/remotedomains');
echo "Local domains contains proadministra.cl: " . (strpos($local, 'proadministra.cl') !== false ? 'YES' : 'NO') . "\n";
echo "Remote domains contains proadministra.cl: " . (strpos($remote, 'proadministra.cl') !== false ? 'YES' : 'NO') . "\n";
