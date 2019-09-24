TE=`date -d "now" +"%Y-%m-%d %H:%M:%S"`

counts=`ps -ef | grep "php ./yii web-socket/test start d" | grep -v grep | wc -l`
echo $counts
if [ $counts -eq 0 ]
 then
 echo  "$DATE web-socket/test is restart"
 cd /var/opt/mount_code/test/basic && php ./yii web-socket/test start d &
else
 echo  "$DATE web-socket/test is running"
fi
