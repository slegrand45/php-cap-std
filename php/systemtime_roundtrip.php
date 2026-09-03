<?php

namespace tests;

function systemtime_roundtrip() {
    $timestamps = [0, 1000, 1609459200, time()];

    foreach ($timestamps as $ts) {
        $st = \StephpCapStdSystemTime::from_unix_timestamp($ts);
        $result = $st->to_unix_timestamp_seconds_utc();
        if ($result === $ts) {
            ok("systemtime: roundtrip for timestamp $ts succeeded");
        } else {
            ko("systemtime: roundtrip for timestamp $ts failed (got $result)");
        }
    }
}
