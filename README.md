整个网站打包按时间备份，包含导出数据库功能。可还原，还原时请先确认压缩包中sql数据库路径和config.php中的配置一致（备份文件后未修改过路径配置就是一致的）。
## 备份还原操作
* 上传backup文件夹到网站根目录(如果网站目录下已有backup目录，请更改备份程序目录后再上传，同时更改config.php中的配置)，备份时直接访问weback.php所在路径。还原时增加file参数，参数值为备份文件夹下备份好的压缩包名(不要路径)。
## config.php
* 数据库连接配置,备份程序所在目录(备份的文件也存放在这里，请确保不要和网站程序目录同名，备份时不会备份此目录)，导出数据库的sql文件名。
## weback.php
* 备份还原网站，备份时直接访问当前文件。还原时增加file参数，参数值为备份文件夹下备份好的压缩包名(不要路径)。
* 默认还原时会先执行备份一次，因为还原过程会删掉数据库和网站目录下的文件，避免还原失败造成数据丢失先执行备份。
* 还原时如果刚备份过不需要重复操作可以临时注释掉备份数据库和网站两行再操作。
## weback.class.php
* 备份还原主要程序