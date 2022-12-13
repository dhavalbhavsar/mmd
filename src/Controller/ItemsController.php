<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Validation;

use App\Controller\ApiController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraints as Assert;

use App\Entity\Items;
use App\Entity\Transaction;
use App\Repository\ItemsRepository;
use Ethereum\Ethereum;
use Ethereum\DataType\SendTransaction;

class ItemsController extends ApiController
{
    /**
     *  Get list of items
     */
    #[Route('/items', name: 'app_items')]
    public function index(
        Request $request, 
        ItemsRepository $itemsRepository
    ): JsonResponse
    {
        
        $items = $itemsRepository->findAll();

        return $this->response([
            'message' => 'Items Listing!',
            'data' => array_map(function($item){
                return [
                    'name' => $item->getName(),
                    'sku' => $item->getSku(),
                    'description' => $item->getDescription(),
                    'short_description' => $item->getShortDescription(),
                    'price' => $item->getPrice(),
                    'mediaFile' => $item->getMediaFile()
                ];
            }, $items)
        ]);
    }

    /**
     *  Create Item (Seller)
     *  @param - JSON body eg. 
     *          {
     *               "name": "Test Item",
     *               "sku": "SKUL-001",
     *               "description": "Test",
     *               "short_description": "Test desc",
     *               "price": 10,
     *               "mediaFile": "https://cdn.pixabay.com/photo/2015/03/10/17/23/youtube-667451_1280.png"
     *           }
     */
    #[Route('/item/create', name: 'app_item_create', methods: 'POST')]
    public function create(
        Request $request,
        ValidatorInterface $validator,
        ManagerRegistry $doctrine
    ): JsonResponse
    {
        $payload = $request->toArray();

        $em = $doctrine->getManager();

        $item = new Items;

        $item->setName($payload['name']);
        $item->setSku($payload['sku']);
        $item->setDescription($payload['description']);
        $item->setShortDescription($payload['short_description']);
        $item->setPrice($payload['price']);
        $item->setMediaFile($payload['mediaFile']);
        $item->setCreatedAt(new \DateTimeImmutable());
        $item->setUpdatedAt(new \DateTimeImmutable());

        $errors = $validator->validate($item, null);  

        if (count($errors) > 0) {
            return $this->respondValidationError($errors, 'Error of validation!');
        }

        $em->persist($item);

        $em->flush();

        return $this->response([
            'message' => 'Item created sucessfully!',
            'data' => [
                'name' => $item->getName(),
                'sku' => $item->getSku(),
                'description' => $item->getDescription(),
                'short_description' => $item->getShortDescription(),
                'price' => $item->getPrice(),
                'media_file' => $item->getMediaFile()
            ]
        ]);
    }

    /**
     *  Setup Auction Item (Seller)
     *  @param - JSON body eg. 
     *           {
     *               "auction_price": 2,
     *               "auction_start_date": "2022-12-12 00:00:00",
     *               "auction_end_date": "2022-12-31 23:59:59"
     *           }
     */
    #[Route('/item/{id}/auction', name: 'app_item_create_auction', methods: 'POST')]
    public function auction(
        $id,
        Request $request,
        ValidatorInterface $validator,
        ManagerRegistry $doctrine
    ): JsonResponse
    {
        $payload = $request->toArray();

        $em = $doctrine->getManager();

        $item = $em->getRepository(Items::class)->find($id);

        if(!$item)
            return $this->respondValidationError([], 'Item not found!');


        $validator = Validation::createValidator();

        $constraint = new Assert\Collection(array(
            'auction_price' => new Assert\NotBlank(),
            'auction_start_date' => [
                new Assert\NotBlank(),
                new Assert\LessThanOrEqual($payload['auction_end_date'])
            ],
            'auction_end_date' => [
                new Assert\NotBlank(),
                new Assert\GreaterThanOrEqual($payload['auction_start_date'])
            ]
        ));

        $violations = $validator->validate($payload, $constraint);

        if ($violations->count() > 0) {
            return $this->respondValidationError($violations, 'Error of validation!');
        }

        $item->setAuctionPrice($payload['auction_price']);
        $item->setAuctionStartingAt( new \DateTimeImmutable($payload['auction_start_date']));
        $item->setAuctionEndingAt( new \DateTimeImmutable($payload['auction_end_date']));

        $em->persist($item);

        $em->flush();

        return $this->response([
            'message' => 'Item updated sucessfully!',
            'data' => [
                'name' => $item->getName(),
                'sku' => $item->getSku(),
                'description' => $item->getDescription(),
                'short_description' => $item->getShortDescription(),
                'price' => $item->getPrice(),
                'media_file' => $item->getMediaFile(),
                'auction_price' => $item->getAuctionPrice()
            ]
        ]);
    }

    /**
     *  Create purchase (Buyer)
     *  @param - JSON body eg. 
     *           {
     *              "from": "0x2e94757df1267f244f4b9ef049416c6794a60552",
     *               "to": "0x22c5071a37432ac845a57c4a69339d161b6baa22"
     *           }
     */
    #[Route('/buy/item/{id}', name: 'app_item_buy', methods: 'POST')]
    public function buy(
        $id,
        Request $request,
        ValidatorInterface $validator,
        ManagerRegistry $doctrine
    ): JsonResponse
    {

        $payload = $request->toArray();

        $em = $doctrine->getManager();

        $item = $em->getRepository(Items::class)->find($id);

        if(!$item)
            return $this->respondValidationError([], 'Item not found!');

        $validator = Validation::createValidator();

        $constraint = new Assert\Collection(array(
            'from' => new Assert\NotBlank(),
            'to' => new Assert\NotBlank()
        ));

        $violations = $validator->validate($payload, $constraint);

        if ($violations->count() > 0) {
            return $this->respondValidationError($violations, 'Error of validation!');
        }

        $amount = $item->getPrice();

        $todayDate = new \DateTimeImmutable();
        $todayTimeStamp = $todayDate->getTimestamp();

        if(!empty($item->getAuctionStartingAt()) && !empty($item->getAuctionEndingAt())) {

            $strartAt = $item->getAuctionStartingAt();
            $startTimestamp = $strartAt->getTimestamp();

            $endingAt = $item->getAuctionEndingAt();
            $endTimestamp = $endingAt->getTimestamp();

            if($startTimestamp >= $todayTimeStamp && $endTimestamp <= $todayTimeStamp) {
                $amount = $item->getAuctionPrice();
            }

        }

        $eth = new Ethereum($this->getParameter('app.eth_url'));

        try {

            $sendTransaction = new SendTransaction(
                new \Ethereum\DataType\EthD20($payload['from']),
                new \Ethereum\DataType\EthD(''),
                new \Ethereum\DataType\EthD20($payload['to']),
                null,
                null,
                new \Ethereum\DataType\EthQ($amount),
                null
            );

            $transaction = $eth->eth_sendTransaction($sendTransaction);

            if(!empty($transaction)) {

                //Create transaction
                $transaction = new Transaction();
                $transaction->setFromAddress($payload['from']);
                $transaction->setToAddress($payload['to']);
                $transaction->setAmount($amount);
                $transaction->setEthTransactionId($transaction['result'] ?? $transaction->result);
                $transaction->setCreatedAt(new \DateTimeImmutable());
                $transaction->setUpdatedAt(new \DateTimeImmutable());

                $em->persist($transaction);
                $em->flush();
            }

        } catch (\Exception $e) {
            return $this->respondValidationError([], 'Purchase failed!');
        }

        return $this->response([
            'message' => 'Purchase sucessfully!',
            'data' => []
        ]);
    }

}
