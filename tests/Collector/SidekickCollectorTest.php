<?php namespace RancherizeBackupStorageboxTests\Collector;

use Mockery;
use Rancherize\Docker\DockerComposeReader\DockerComposeReader;
use Rancherize\Docker\DockerComposerVersionizer;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\NamedVolumeCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\NewNamesCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\StorageboxData;
use RancherizeBackupStoragebox\Backup\PODBackup;
use RancherizeBackupStorageboxTests\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SidekickCollectorTest
 */
class SidekickCollectorTest extends TestCase {

	protected $file;
	public function __construct() {
		$this->file =
"version: '2'
volumes:
  pxc-mysql-2017-06-13-062348:
    driver: local
  pxc-conf-2017-05-18-083233:
    driver: local
  pxc-mysql-2017-07-24-073541:
    driver: local
  pxc-entrypoint-2017-09-04-063000:
    driver: local
  pxc-mysql-2017-05-18-083233:
    driver: local
  pxc-entrypoint-2017-06-13-062348:
    driver: local
  pxc-mysql-2017-04-26-113101:
    driver: local
  pxc-conf-2017-07-05-114803:
    driver: local
  pxc-conf-2017-07-24-073541:
    driver: local
  pxc-conf-2017-04-26-113101:
    driver: local
  pxc-entrypoint-2017-04-26-113101:
    driver: local
  pxc-conf-2017-06-13-062348:
    driver: local
  pxc-entrypoint-2017-07-05-114803:
    driver: local
  pxc-mysql-2017-07-05-114803:
    driver: local
  pxc-entrypoint-2017-07-24-073541:
    driver: local
  pxc-entrypoint-2017-05-18-083233:
    driver: local
services:
  pxc-server:
    image: flowman/percona-xtradb-cluster:5.6.28-1
    environment:
      MYSQL_DATABASE: ''
      MYSQL_PASSWORD: ''
      MYSQL_ROOT_PASSWORD: iproot
      MYSQL_USER: ''
      PXC_SST_PASSWORD: ipsst
    entrypoint:
    - bash
    - -x
    - /opt/rancher/start_pxc
    network_mode: container:pxc
    volumes_from:
    - pxc-data
    labels:
      io.rancher.container.hostname_override: container_name
  phpmyadmin:
    image: phpmyadmin/phpmyadmin:4.7
    environment:
      MYSQL_ROOT_PASSWORD: iproot
    links:
    - pxc:db
    labels:
      traefik.frontend.rule: Host:pma.services.ahgz.website
      traefik.enable: 'true'
      io.rancher.scheduler.affinity:container_label_ne: io.rancher.stack_service.name=$\${stack_name}/$\${service_name}
      io.rancher.scheduler.affinity:host_label: pma=true
      traefik.port: '80'
      io.rancher.container.pull_image: always
  Backup-Incremental:
    image: ipunktbs/xtrabackup:1.1.0
    environment:
      BACKUP_DRIVER: convoy
      BACKUP_VOLUME: backup
      BACKUP_MODE: INCREMENTAL
      MYSQL_PASSWORD: iproot
      MYSQL_USER: root
      RANCHER_ACCESS_KEY: xxxxxxx
      RANCHER_SECRET_KEY: xxxxxxx
      RANCHER_URL: .....
      RESTORE_STACK: Database-Cluster-Backups
      STORAGEBOX_PASSWORD: .....
      STORAGEBOX_URL: ......
      STORAGEBOX_USER: u150607
    stdin_open: true
    volumes:
    - /mnt/backup/backup:/target
    - /var/pxc-cluster/data:/var/lib/mysql
    tty: true
    links:
    - pxc:target
    command:
    - backup
    labels:
      cron.schedule: 0 0 6-20/6 * * 1-5
      io.rancher.scheduler.affinity:host_label: backup=storagebox
      io.rancher.container.start_once: 'true'
      io.rancher.container.pull_image: always
      io.rancher.scheduler.affinity:container_label: io.rancher.stack_service.name=database-cluster/pxc/pxc-data
      io.rancher.scheduler.affinity:host_label_soft: backups=true
  pxc-data:
    image: flowman/percona-xtradb-cluster:5.6.28-1
    environment:
      MYSQL_ALLOW_EMPTY_PASSWORD: 'yes'
    network_mode: none
    volumes:
    - pxc-entrypoint:/docker-entrypoint-initdb.d
    - /var/pxc-cluster/conf:/etc/mysql/conf.d
    - /var/pxc-cluster/data:/var/lib/mysql
    command:
    - /bin/true
    labels:
      io.rancher.container.start_once: 'true'
  Backup-Full:
    image: ipunktbs/xtrabackup:1.1.0
    environment:
      BACKUP_DRIVER: convoy
      BACKUP_VOLUME: backup
      BACKUP_MODE: FULL
      MYSQL_PASSWORD: iproot
      MYSQL_USER: root
      RANCHER_ACCESS_KEY: xxxxx
      RANCHER_SECRET_KEY: xxxxx
      RANCHER_URL: xxxxxxxx
      RESTORE_STACK: Database-Cluster-Backups
      STORAGEBOX_PASSWORD: .....
      STORAGEBOX_URL: .....
      STORAGEBOX_USER: u150607
    stdin_open: true
    volumes:
    - /mnt/backup/backup:/target
    - /var/pxc-cluster/data:/var/lib/mysql
    tty: true
    links:
    - pxc:target
    command:
    - backup
    labels:
      cron.schedule: 0 0 2 * * 0
      io.rancher.scheduler.affinity:host_label: backup=storagebox
      io.rancher.container.start_once: 'true'
      io.rancher.container.pull_image: always
      io.rancher.scheduler.affinity:container_label: io.rancher.stack_service.name=database-cluster/pxc/pxc-data
      io.rancher.scheduler.affinity:host_label_soft: backups=true
  pxc:
    image: flowman/percona-xtradb-cluster-confd:v0.2.0
    volumes_from:
    - pxc-data
    labels:
      io.rancher.scheduler.affinity:host_label: database=true
      io.rancher.sidekicks: pxc-server,pxc-clustercheck,pxc-data
      io.rancher.container.hostname_override: container_name
      io.rancher.scheduler.affinity:container_label_ne: io.rancher.stack_service.name=$\${stack_name}/$\${service_name}
  pxc-clustercheck:
    image: flowman/percona-xtradb-cluster-clustercheck:v2.0
    network_mode: container:pxc
    volumes_from:
    - pxc-data
    labels:
      io.rancher.container.hostname_override: container_name
  PXC-Loadbalancer:
    image: rancher/lb-service-haproxy:v0.7.9
    expose:
    - 3306:3306/tcp
    labels:
      io.rancher.scheduler.affinity:host_label: database=true
      io.rancher.container.agent.role: environmentAdmin
      io.rancher.container.create_agent: 'true'
      io.rancher.scheduler.global: 'true'";
		parent::__construct();
	}


	public function testCollected(  ) {
		$dockerComposeReader = new DockerComposeReader();
		$composeVersionizer = new DockerComposerVersionizer();

		$databaseService = 'pxc';
		$composeData = $dockerComposeReader->read($this->file);
		$composeParser = $composeVersionizer->parse($composeData);

		$service = $composeParser->getService($databaseService, $composeData);
		$sidekicks = $composeParser->getSidekicks($databaseService, $service, $composeData);

		$storageboxData = new StorageboxData();
		$storageboxData->setSidekicks($sidekicks);

		$namedVolumeCollector = new NamedVolumeCollector();

		/**
		 * @var Mockery\MockInterface|InputInterface $input
		 */
		$input = Mockery::mock(InputInterface::class);
		/**
		 * @var Mockery\MockInterface|OutputInterface $output
		 */
		$output = Mockery::mock (OutputInterface::class);
		$namedVolumeCollector->collect($input, $output, $storageboxData);

		$storageboxData->setBackupKey('full-test');
		$backup = new PODBackup();
		$backup->setServiceName($databaseService);
		$storageboxData->setBackup( $backup );
		$newNamesCollector = new NewNamesCollector();
		$newNamesCollector->collect($input, $output, $storageboxData);

		$this->assertEquals('/var/pxc-cluster/data-full-test', $storageboxData->getNewMysqlVolumeName() );
	}


}