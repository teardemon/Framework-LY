# Author: LyonWong
# Date: 2014-09-02
# Desc: Common source of bin

PATH_ROOT=`cd $(dirname $0)/../;pwd`

for dir in default common local; do
    cfsh=$PATH_ROOT/config/$dir/init.sh
    if [ -f $cfsh ]; then
        source $cfsh
    fi
done

