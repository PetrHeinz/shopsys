<?php

namespace SS6\ShopBundle\Model\Product;

use Doctrine\ORM\EntityManager;
use SS6\ShopBundle\Model\Product\Parameter\ParameterRepository;
use SS6\ShopBundle\Model\Product\Parameter\ProductParameterValue;
use SS6\ShopBundle\Model\Product\Product;
use SS6\ShopBundle\Model\Product\ProductRepository;
use SS6\ShopBundle\Model\Product\ProductVisibilityFacade;

class ProductEditFacade {

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;
	
	/**
	 * @var \SS6\ShopBundle\Model\Product\ProductRepository
	 */
	private $productRepository;
	
	/**
	 * @var \SS6\ShopBundle\Model\Product\ProductVisibilityFacade
	 */
	private $productVisibilityFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Parameter\ParameterRepository
	 */
	private $parameterRepository;

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 * @param \SS6\ShopBundle\Model\Product\ProductRepository $productRepository
	 */
	public function __construct(
		EntityManager $em,
		ProductRepository $productRepository,
		ProductVisibilityFacade $productVisibilityFacade,
		ParameterRepository $parameterRepository
	) {
		$this->em = $em;
		$this->productRepository = $productRepository;
		$this->productVisibilityFacade = $productVisibilityFacade;
		$this->parameterRepository = $parameterRepository;
	}
	
	/**
	 * @param \SS6\ShopBundle\Model\Product\ProductData $productData
	 * @return \SS6\ShopBundle\Model\Product\Product
	 */
	public function create(ProductData $productData) {
		$product = new Product($productData);

		$this->em->persist($product);
		$this->saveParameters($product, $productData->getParameters());

		$this->em->flush();
		
		$this->productVisibilityFacade->refreshProductsVisibilityDelayed();
		
		return $product;
	}
	
	/**
	 * @param int $productId
	 * @param \SS6\ShopBundle\Model\Product\ProductData $productData
	 * @return \SS6\ShopBundle\Model\Product\Product
	 */
	public function edit($productId, ProductData $productData) {
		$product = $this->productRepository->getById($productId);

		$product->edit($productData);
		$this->saveParameters($product, $productData->getParameters());

		$this->em->flush();
		
		$this->productVisibilityFacade->refreshProductsVisibilityDelayed();
		
		return $product;
	}
	
	/**
	 * @param int $productId
	 */
	public function delete($productId) {
		$product = $this->productRepository->getById($productId);
		$this->em->remove($product);
		$this->em->flush();
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\Product $product
	 * @param \SS6\ShopBundle\Model\Product\Parameter\ProductParameterValueData[] $productParameterValuesData
	 */
	private function saveParameters(Product $product, array $productParameterValuesData) {
		// Doctrine runs INSERTs before DELETEs in UnitOfWork. In case of UNIQUE constraint
		// in database, this leads in trying to insert duplicate entry.
		// That's why it's necessary to do remove and flush first.

		$oldProductParameterValues = $this->parameterRepository->findParameterValuesByProduct($product);
		foreach ($oldProductParameterValues as $oldProductParameterValue) {
			$this->em->remove($oldProductParameterValue);
		}
		$this->em->flush();

		foreach ($productParameterValuesData as $productParameterValueData) {
			$productParameterValueData->setProduct($product);
			$productParameterValue = new ProductParameterValue($productParameterValueData);
			$this->em->persist($productParameterValue);
		}
		$this->em->flush();
	}

}
