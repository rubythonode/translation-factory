@if (! isset($parentKey))
    @php $parentKey = '' @endphp
@endif

<li class="key" data-key="{{ $parentKey.$itemKey }}">
    <a href="@if (! is_array($itemValue)){{ url('translation-factory/file/'.$translationBag->getHash().'/item/'.$parentKey.$itemKey) }}@endif">
        <div class="container">
            <div class="columns">
                <div class="column col-4">
                    <div class="text-gray text-ellipsis" title="{{ $parentKey.$itemKey }}">
                        @if (isset($arrayLevel))
                            &nbsp;&nbsp; @php echo str_repeat('&middot;', $arrayLevel * 4) @endphp
                        @endif
                        <span class="label label-rounded">{{ $itemKey }}</span>
                    </div>
                </div>
                <div class="column col-7">
                    @if (is_array($itemValue))
                        <span class="label badge" data-badge="{{ sizeof($itemValue) }}">
                            Array
                        </span>
                    @else
                        <div class="text-gray text-ellipsis" title="{{ $itemValue }}">
                            {{ $itemValue }}
                        </div>
                    @endif
                </div>
                <div class="column col-1">
                    @if (! is_array($itemValue))
                        @if ($translationBag->getTranslation($targetLanguage, $parentKey.$itemKey) != '')
                            <span class="is-translated text-gray" title="Translated">✓</span>
                        @else
                            <span class="is-not-translated text-gray" title="Not yet translated">🞪</span>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </a>
</li>

@if (is_array($itemValue))
    @if (isset($arrayLevel))
        @php $arrayLevel++ @endphp
    @else
        @php $arrayLevel = 1 @endphp
    @endif

    @php $parentKey .= $itemKey.'.' @endphp
    @php $subItems = $itemValue @endphp
    @foreach($subItems as $itemKey => $itemValue)
        @include('translationFactory::items_list_item')
    @endforeach
@endif