<?php
// 日志配置
return [
    // 日志记录方式，内置 file socket 支持扩展
    'type'        => 'File',
    // 日志保存目录
    'path'        => __DIR__ . '/../logs/',
    // 单个文件大小限制
    'file_size' => 2097152,
    // 日志时间格式
    'time_format' => 'c',
    // 日志记录级别
    'level'       => [],
    // 单文件日志写入
    'single'      => false,
    // 独立日志级别
    'apart_level' => [],
    // 最大日志文件数量
    'max_files'   => 14,
    'json'        => true,
    // 是否关闭日志写入
    'close'       => false,
];
