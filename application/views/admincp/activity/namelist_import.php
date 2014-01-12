<div class="row-fluid">
    <div class="span12">
        <div class="social-box">
            <div class="body">
                <div class="row-fluid">
                    <div class="span12 text-center">
                        <input type="file" name="filedata" id="uploader">
                    </div>
                </div>
                <hr>
                <div class="row-fluid">
                    <div class="span12">
                        <table id="filelist" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>上传记录</th>
                                    <th>文件类型</th>
                                    <th>文件大小</th>
                                    <th>上传时间</th>
                                    <th>导入数据库</th>
                                </tr>
                            </thead>
                            <tbody>
<?php if ( empty($attachments) ): ?>
                        <tr>
                            <td colspan="6">无记录</td>
                        </tr>
<?php else: ?>
<?php foreach($attachments as $attachment): ?>
                                <tr>
                                    <td><?php echo $attachment['id']; ?></td>
                                    <td><?php echo $attachment['orig_name']; ?></td>
                                    <td><?php echo $attachment['file_type']; ?></td>
                                    <td><?php echo byte_format($attachment['file_size']); ?></td>
                                    <td><span data-toggle="tooltip" title="<?php echo date('y-m-d h:i:s', $attachment['created']); ?>"><?php echo time_past($attachment['created']); ?></span></td>
                                    <td>
                                        <a href="<?php echo base_url('admincp/activity/sheets/'.$aid.'/'.$attachment['id']); ?>" class="btn btn-primary">导入数据库</a>
                                        <a href="<?php echo base_url('member/attachment/destroy/'.$attachment['id']); ?>" class="btn btn-danger" onclick="return confirm('该操作不可恢复！删除该文件？');">删除文件</a>
                                    </td>
                                </tr>
<?php endforeach; ?>
<?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
