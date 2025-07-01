import React from 'react';
import { FileUpload, FileUploadItem } from '@/components/ui/file-upload';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Package, Image as ImageIcon } from 'lucide-react';

interface RachmaFileUploadProps {
  rachmaFiles: FileUploadItem[];
  onRachmaFilesChange: (files: FileUploadItem[]) => void;
  previewFiles: FileUploadItem[];
  onPreviewFilesChange: (files: FileUploadItem[]) => void;
  disabled?: boolean;
  errors?: {
    files?: string;
    preview_images?: string;
  };
}

const RACHMA_FILE_FORMATS = [
  { ext: 'dst', name: 'Tajima DST', color: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' },
  { ext: 'pes', name: 'Brother PES', color: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' },
  { ext: 'jef', name: 'Janome JEF', color: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' },
  { ext: 'exp', name: 'Melco EXP', color: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' },
  { ext: 'vp3', name: 'Husqvarna VP3', color: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' },
  { ext: 'xxx', name: 'Singer XXX', color: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' },
  { ext: 'hus', name: 'Husqvarna HUS', color: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200' },
  { ext: 'vip', name: 'Pfaff VIP', color: 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200' },
  { ext: 'sew', name: 'Janome SEW', color: 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200' },
  { ext: 'csd', name: 'Singer CSD', color: 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-200' },
  { ext: 'pdf', name: 'PDF Pattern', color: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' },
  { ext: 'zip', name: 'ZIP Archive', color: 'bg-slate-100 text-slate-800 dark:bg-slate-900 dark:text-slate-200' },
  { ext: 'rar', name: 'RAR Archive', color: 'bg-stone-100 text-stone-800 dark:bg-stone-900 dark:text-stone-200' },
];

export function RachmaFileUpload({
  rachmaFiles,
  onRachmaFilesChange,
  previewFiles,
  onPreviewFilesChange,
  disabled = false,
  errors = {}
}: RachmaFileUploadProps) {
  
  return (
    <div className="space-y-6">
      {/* Rachma Files Upload */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Package className="w-5 h-5" />
            ملفات الرشمة *
            <Badge variant="secondary" className="text-xs">
              {rachmaFiles.length} ملف
            </Badge>
          </CardTitle>
        </CardHeader>
        <CardContent>
          <FileUpload
            files={rachmaFiles}
            onFilesChange={onRachmaFilesChange}
            accept=".zip,.rar,.dst,.exp,.jef,.pes,.vp3,.xxx,.hus,.vip,.sew,.csd,.pdf"
            maxSize={10}
            maxFiles={20}
            multiple={true}
            allowReorder={true}
            showPreviews={false}
            disabled={disabled}
            title="ملفات التطريز"
            description="اسحب ملفات التطريز هنا أو اضغط للاختيار"
            uploadText="اختيار ملفات التطريز"
            dragText="اسحب ملفات التطريز هنا"
          />
          
          {errors.files && (
            <p className="text-sm text-destructive mt-2">{errors.files}</p>
          )}
          
          {/* Supported Formats Info */}
          <div className="mt-4 p-4 bg-muted/50 rounded-lg">
            <h4 className="text-sm font-medium mb-3">الصيغ المدعومة:</h4>
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
              {RACHMA_FILE_FORMATS.map((format) => (
                <Badge
                  key={format.ext}
                  variant="secondary"
                  className={`text-xs justify-center ${format.color}`}
                >
                  .{format.ext.toUpperCase()}
                </Badge>
              ))}
            </div>
            <p className="text-xs text-muted-foreground mt-2">
              يمكنك رفع ملفات بصيغ مختلفة لنفس التصميم لتوفير خيارات أكثر للعملاء
            </p>
          </div>
        </CardContent>
      </Card>

      {/* Preview Images Upload */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <ImageIcon className="w-5 h-5" />
            صور المعاينة
            <Badge variant="outline" className="text-xs">
              اختياري
            </Badge>
            {previewFiles.length > 0 && (
              <Badge variant="secondary" className="text-xs">
                {previewFiles.length} صورة
              </Badge>
            )}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <FileUpload
            files={previewFiles}
            onFilesChange={onPreviewFilesChange}
            accept="image/*"
            maxSize={2}
            maxFiles={10}
            multiple={true}
            allowReorder={true}
            showPreviews={true}
            disabled={disabled}
            title="صور المعاينة"
            description="اسحب الصور هنا أو اضغط للاختيار"
            uploadText="اختيار الصور"
            dragText="اسحب الصور هنا"
          />
          
          {errors.preview_images && (
            <p className="text-sm text-destructive mt-2">{errors.preview_images}</p>
          )}
          
          {/* Preview Images Info */}
          <div className="mt-4 p-4 bg-muted/50 rounded-lg">
            <h4 className="text-sm font-medium mb-2">نصائح لصور المعاينة:</h4>
            <ul className="text-xs text-muted-foreground space-y-1">
              <li>• استخدم صور عالية الجودة لإظهار تفاصيل التطريز</li>
              <li>• أضف صور من زوايا مختلفة لإعطاء فكرة شاملة</li>
              <li>• تأكد من وضوح الألوان والتفاصيل</li>
              <li>• الصور الأولى ستظهر كصورة رئيسية</li>
            </ul>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
