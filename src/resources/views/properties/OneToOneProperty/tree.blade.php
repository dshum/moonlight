@foreach ($treeItemList as $itemName => $item)
	<div class="item">{{ $item->getTitle() }}</div>
	@foreach ($treeItemElementList[$itemName] as $element)
		<div class="tree">
		@if (isset($treeView[$element->getClassId()]))
			<div class="minus" node1="{{ $element->getClassId() }}" opened="true"></div>
		@elseif (isset($treeCount[$element->getClassId()]) && $treeCount[$element->getClassId()] > 0)
			<div class="plus" node1="{{ $element->getClassId() }}" itemName="{{ $currentProperty->getItem()->getName() }}" propertyName="{{ $currentProperty->getName() }}" opened="open"></div>
		@else
			<div class="empty"></div>
		@endif
		@if ($itemName == $currentProperty->getRelatedClass())
			{{ Form::radio($currentProperty->getName(), $element->id, $element->equalTo($value) ? true : false, array('id' => $currentProperty->getName().'_'.$element->id, 'onetoone' => 'radio')) }} {{ Form::label($currentProperty->getName().'_'.$element->id, $element->{$item->getMainProperty()}) }}<br />
		@else
			<span>{{ $element->{$item->getMainProperty()} }}</span><br />
		@endif
			<div class="padding{{ isset($parents[$element->getClassId()]) ? '' : ' dnone' }}" node1="{{ $element->getClassId() }}">
			{{ isset($treeView[$element->getClassId()]) ? $treeView[$element->getClassId()] : null }}
			</div>
		</div>
	@endforeach
@endforeach