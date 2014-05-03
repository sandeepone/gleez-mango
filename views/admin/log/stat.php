<div class="help">
    <?php _e('For DB admins only. This shows variety of storage statistics for a given collection.'); ?>
</div>

<table id="log-admin-view" class="table table-striped table-bordered table-highlight">
    <colgroup><col class="oce-first"></colgroup>
    <thead>
    <tr>
        <th><?php _e('Field')?></th>
        <th><?php _e('Value')?></th>
        <th><?php _e('Description')?></th>
    </tr>
    </thead>
    <tbody>
        <tr>
            <td><var>ns</var></td>
            <td><?php echo $stats['ns']; ?></td>
            <td><?php _e('The namespace of the current collection, which follows the format [database].[collection]'); ?></td>
        </tr>
        <tr>
            <td><var>count</var></td>
            <td><?php echo $stats['count']; ?></td>
            <td><?php _e('The number of objects or documents in this collection.'); ?></td>
        </tr>
        <tr>
            <td><var>size</var></td>
            <td><?php _e(':size KB', array(':size' => $stats['size'])); ?></td>
            <td><?php _e('The size of the data stored in this collection. This value does not include the size of any indexes associated with the collection'); ?></td>
        </tr>
        <tr>
            <td><var>avgObjSize</var></td>
            <td><?php _e(':size KB', array(':size' => $stats['avgObjSize'])); ?></td>
            <td><?php _e('The average size of an object in the collection.'); ?></td>
        </tr>
        <tr>
            <td><var>storageSize</var></td>
            <td><?php _e(':size KB', array(':size' => $stats['storageSize'])); ?></td>
            <td><?php _e('The total amount of storage allocated to this collection for document storage.'); ?></td>
        </tr>
        <tr>
            <td><var>numExtents</var></td>
            <td><?php echo $stats['numExtents']; ?></td>
            <td><?php _e('The total number of contiguously allocated data file regions.'); ?></td>
        </tr>
        <tr>
            <td><var>nindexes</var></td>
            <td><?php echo $stats['nindexes']; ?></td>
            <td><?php _e('The number of indexes on the collection. All collections have at least one index on the _id field.'); ?></td>
        </tr>
        <tr>
            <td><var>lastExtentSize</var></td>
            <td><?php _e(':size KB', array(':size' => $stats['lastExtentSize'])); ?></td>
            <td><?php _e('The size of the last extent allocated.'); ?></td>
        </tr>
        <tr>
            <td><var>paddingFactor</var></td>
            <td><?php echo $stats['paddingFactor']; ?></td>
            <td><?php _e('The amount of space added to the end of each document at insert time. The document padding provides a small amount of extra space on disk to allow a document to grow slightly without needing to move the document.'); ?></td>
        </tr>
        <tr>
            <td><var>systemFlags</var></td>
            <td><?php echo $stats['systemFlags']; ?></td>
            <td><?php _e('Reports the flags on this collection that reflect internal server options. Typically this value is 1 and reflects the existence of an index on the _id field.'); ?></td>
        </tr>
        <tr>
            <td><var>userFlags</var></td>
            <td><?php echo $stats['userFlags']; ?></td>
            <td><?php _e('Reports the flags on this collection set by the user.'); ?></td>
        </tr>
        <tr>
            <td><var>totalIndexSize</var></td>
            <td><?php _e(':size KB', array(':size' => $stats['totalIndexSize'])); ?></td>
            <td><?php _e('The total size of all indexes.'); ?></td>
        </tr>
        <tr>
            <td><var>indexSizes</var></td>
            <td>
                <?php foreach($stats['indexSizes'] as $index => $size): ?>
                    <strong><?php echo $index; ?></strong>: <?php _e(':size KB', array(':size' => $size)); ?><br>
                <?php endforeach; ?>
            </td>
            <td><?php _e('This field specifies the key and size of every existing index on the collection.'); ?></td>
        </tr>
        <?php if (isset($stats['capped'])): ?>
            <tr>
                <td><var>capped</var></td>
                <td><?php echo $stats['capped']; ?></td>
                <td><?php _e('Capped collection. If the collection should be a fixed size.') ?></td>
            </tr>
            <tr>
                <td><var>max</var></td>
                <td><?php echo $stats['max']; ?></td>
                <td><?php _e('If the collection is fixed size, the maximum number of elements to store in the collection.') ?></td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>