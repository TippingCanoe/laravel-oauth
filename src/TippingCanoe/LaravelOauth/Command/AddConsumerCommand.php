<?php namespace TippingCanoe\LaravelOauth\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use TippingCanoe\LaravelOauth\Repository\Consumer as ConsumerRepository;


class AddConsumerCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'oauth:add-consumer';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Add a new consumer for oauth access.';

	/** @var \TippingCanoe\LaravelOauth\Repository\Consumer */
	protected $consumerRepository;

	/**
	 * @param \TippingCanoe\LaravelOauth\Repository\Consumer $consumerRepository
	 * @internal param \Illuminate\Filesystem\Filesystem $files
	 * @return \TippingCanoe\LaravelOauth\Command\AddConsumerCommand
	 */
	public function __construct(
		ConsumerRepository $consumerRepository
	) {
		$this->consumerRepository = $consumerRepository;
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire() {
		$consumer = $this->consumerRepository->findOrCreateByName($this->argument('name'));
		$this->info(sprintf('Consumer %s created.', $consumer->name));
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments() {
		return [
			['name', InputArgument::REQUIRED, 'The name of the consumer.']
		];
	}
	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()	{
		return [];
	}

}