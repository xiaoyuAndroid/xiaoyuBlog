<?php
/**
 * Created by PhpStorm.
 * User: xiaoyu
 * Date: 2017/9/10
 * Time: 19:13
 */


/**
 * mysql.php mysql系列操作函数
 */

/**
 * 连接数据库
 *
 * @return resource 连接成功,返回连接数据库的资源
 */

function mConn() {
    static $conn = null;
    if($conn === null) {
        $cfg = require(ROOT . '/lib/config.php');
        $conn = mysql_connect($cfg['host'] , $cfg['user'] , $cfg['pwd']);
        if ( !$conn ) {
            die("Could not connect: ".mysql_error());
        }
        mysql_select_db($cfg['db'] , $conn);
        mysql_query('set names '.$cfg['charset'] , $conn);
    }
    return $conn;
}

/**
 * mQuery 查询函数
 * 将 mysql_query() 封装起来
 *
 * @return mixed resoure/bool
 */
function mQuery($sql) {
    $rs  = mysql_query($sql , mConn());
    if($rs) {
        mLog($sql);
    } else {
        mLog($sql. "\n" . mysql_error());
    }

    return $rs;
}


/**
 * log日志记录功能
 * @param str $str 待记录的字符串
 */

function mLog($str) {
    date_default_timezone_set('PRC');
    $filename = ROOT . '/log/' . date('Ymd',time()) . '.txt';
    $log = "-----\n".date('Y/m/d H:i:s',time()) . " \n" . $str . "\n" . "-----\n\n";
    return file_put_contents($filename, $log , FILE_APPEND);
}


/**
 * mGetAll 查询多行数据
 *
 * @param str $sql select 待查询的sql语句
 * @return mixed select 查询成功,返回二维数组;失败返回false
 */

function mGetAll($sql) {
    $rs = mQuery($sql);
    if(!$rs) {
        return false;
    }

    $data = array();
    while($row = mysql_fetch_assoc($rs)) {
        $data[] = $row;
    }

    return $data;
}

/**
 * mGetRow 取出一行数据
 *
 * @param str $sql 待查询的sql语句
 * @return arr  查询成功 返回一个一维数组；失败返回 false
 */

function mGetRow($sql) {
    $rs = mQuery($sql);
    if(!$rs) {
        return false;
    }

    return mysql_fetch_assoc($rs);
}

/**
 * mGetOne 查询返回一个结果
 *
 * @param str $sql 待查询的select语句
 * @return mixed 成功:返回结果,失败:返回false
 */
function mGetOne($sql) {
    $rs = mQuery($sql);
    if(!$rs) {
        return false;
    }

    return mysql_fetch_row($rs)[0];
}

/**
 * 自动拼接insert 和 update sql语句,并且调用mQuery() 去执行sql
 *
 * @param str $table 表名
 * @param arr $data 接收到的数据,一维数组
 * @param str $act 动作 默认为'insert'
 * @param str $where 防止update更改时少加where条件
 * @return bool insert 或者update 插入成功或失败
 *
 */

function mExec($table , $data , $act='insert' , $where=0) {
    if($act == 'insert') {
        $sql = "insert into $table (";
        $sql .= implode(',' , array_keys($data) ) . ") values ('";
        $sql .= implode("','" , array_values($data) ) . "')";
        return mQuery($sql);
    } else if ($act == 'update') {
        $sql = "update $table set ";
        foreach($data as $k=>$v) {
            $sql .= $k . "='" . $v . "',";
        }

        $sql = rtrim($sql , ',') . " where ".$where;
        return mQuery($sql);
    }
}
//$data = array('title'=>'今天的空气' , 'content'=>'空气质量优' , 'pubtime'=>12345678,'author'=>'baibai');
//insert into art (title,content,pubtime,author) values ('今天的空气','空气质量优','12345678','baibai');
//update art set title='今天的空气',conte='空气质量优',pubtime='12345678',author='baibai' where art_id=1;
//cho mExec('art' , $data , 'update' , 'art_id=1');;
//insert into cat (id,catname) values (5 , 'test');

/**
 * getLastId 取得上一步insert 操作产生的主键id
 */
function getLastId() {
    return mysql_insert_id(mConn());
}
