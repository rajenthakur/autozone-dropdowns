<?php
namespace Drupal\custom_entity_product\Plugin\Block;
use Drupal\Core\Block\BlockBase;


/**
* Provides a block with a simple text.
*
* @Block(
*   id = "product_qr_block",
*   admin_label = @Translation("Product QR Code block"),
*   category = "custom"
* )
*/
class ProductQRBlock extends BlockBase {

 /**
  * {@inheritdoc}
  */
 public function build() {
    $url = '';
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      if($node->hasField('field_purchase_link') && !$node->get('field_purchase_link')->isEmpty()){
        $url = $node->get('field_purchase_link')->getValue()[0]['uri'];
      }

    }
    $contents['url'] = $url;
    return [
      '#markup' => 'QRcode',
      '#theme' => 'product_qr_block',
      '#contents' => $contents,
      '#attached' => [
        'library' => [
          'custom_entity_product/cutom_qr_product',
        ],
      ],
    ];
  }

}