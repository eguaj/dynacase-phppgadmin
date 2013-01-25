VERSION=5.0.4
RELEASE=1

webinst:
	mkdir -p tmp
	tar -C src -zcf tmp/content.tar.gz .
	cp info.xml tmp/info.xml
	tar -C tmp -zcf dynacase-phppgadmin-${VERSION}-${RELEASE}.webinst info.xml content.tar.gz

clean:
	rm -Rf tmp
	rm -f dynacase-phppgadmin-*.webinst
