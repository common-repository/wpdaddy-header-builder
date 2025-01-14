<?php

namespace WPDaddy\Dom;

use Exception;

/**
 * Represents a minimal document object that has no parent. It is used as a
 * light-weight version of Document to store well-formed or potentially
 * non-well-formed fragments of XML.
 *
 * Various other methods can take a document fragment as an argument (e.g.,
 * any Node interface methods such as Node.appendChild and Node.insertBefore),
 * in which case the children of the fragment are appended or inserted, not
 * the fragment itself.
 *
 * This interface is also of great use with Web components: <template>
 * elements contains a DocumentFragment in their HTMLTemplateElement::$content
 * property.
 *
 * An empty DocumentFragment can be created using the
 * Document::createDocumentFragment() method or the constructor.
 */
class DocumentFragment extends \DOMDocumentFragment {
	use LiveProperty, ParentNode;

	/**
	 * @param string $data
	 *
	 * @return bool
	 */
	public function appendHTML($data){
		try {
			$tempDocument = new HTMLDocument($data);

			foreach($tempDocument->body->children as $child) {
				$node = $this->ownerDocument->importNode(
					$child,
					true
				);
				$this->appendChild($node);
			}

			return true;
		} catch(Exception $exception) {
			return false;
		}
	}

	/**
	 * @return \DOMDocument
	 */
	protected function getRootDocument(){
		return $this->ownerDocument;
	}
}
