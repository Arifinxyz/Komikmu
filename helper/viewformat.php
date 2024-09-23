<?php
function formatView($views) {
    if ($views >= 1000000000) {
        return number_format($views / 1000000000, 1) . 'B';
    } elseif ($views >= 1000000) {
        return number_format($views / 1000000, 1) . 'M';
    } elseif ($views >= 1000) {
        return number_format($views / 1000, 1) . 'K';
    } else {
        return $views;
    }
}