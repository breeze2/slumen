## 协程客户端

*Swoole 4.x**中提供了丰富的[协程客户端](https://wiki.swoole.com/wiki/page/1005.html)，如Redis客户端、MySQL客户端、TCP/UDP/Unix客户端和Http/WebSocket/Http2客户端等等，使用这些组件可以很方便地实现高性能的并发编程。

目前**Slumen**只实现了MySQL协程客户端与**Lumen**的结合——[MySQL协程客户端连接池](/3_coroutine_client_feature)。
