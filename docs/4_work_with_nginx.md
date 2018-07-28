# 与NginX

NginX代理：
```conf
server {
    root /var/www/html;
    server_name www.domain.com;

    location / {
        proxy_http_version 1.1;
        proxy_set_header Connection "keep-alive";
        proxy_set_header X-Real-IP $remote_addr;
        if (!-e $request_filename) {
             proxy_pass http://127.0.0.1:9080;
        }
    }
}
```