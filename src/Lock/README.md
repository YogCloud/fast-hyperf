# 组件说明

 - 实现锁的key统一规范
 - 实现锁的基本方法
    - 锁获取等待
    - 锁获取
    - 锁释放
    
# 依赖

 - redis
 
# 使用方法

- `RedisLock`
```php
// 默认name为default 传递服务名称也可不传
$lock = new RedisLock('GoodService');
// 锁定该id商品
$lock->lock(100);
// 解锁
$lock->unlock(100);
```


## 正确用法

```php
for ($i = 1; $i <= 1000; $i++) {
   co(function () {
       $lock = di(RedisLock::class);
       if ($lock->lock('test123', 100)) {
           try {
               $this->info("我是协程" . Coroutine::getCid() . '我获取到锁了');
               Coroutine::sleep(10);
           } finally {
               $this->info("我是协程" . Coroutine::getCid() . '我释放锁');
               $lock->unlock('test123');
           }
       } else {
           $this->info("我是协程" . Coroutine::getCid() . '我没获取到锁');
           Coroutine::sleep(20);
       }
   });
}
```