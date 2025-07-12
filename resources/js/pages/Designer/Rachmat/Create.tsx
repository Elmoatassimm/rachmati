import React, { useState, useEffect } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/app-layout';
import { DesignerPageHeader } from '@/components/designer/DesignerPageHeader';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { ArrowLeft, Plus, Minus, ChevronDown, FileText, Package, Image as ImageIcon, Sparkles, ArrowRight, AlertTriangle } from 'lucide-react';
import { Category, PageProps, PartsSuggestion } from '@/types';
import { Checkbox } from '@/components/ui/checkbox';
import { Alert, AlertDescription } from '@/components/ui/alert';

interface Props extends PageProps {
  categories: Category[];
  partsSuggestions?: PartsSuggestion[];
}

interface RachmaPart {
  name_ar: string;
  name_fr: string;
  length: string;
  height: string;
  stitches: string;
}

export default function Create({ categories, partsSuggestions = [] }: Props) {
  // Current step state
  const [currentStep, setCurrentStep] = useState(1);
  const totalSteps = 3;

  // Get page props to access errors
  const page = usePage<{
    errors: Record<string, string>;
  }>();

  // Access errors from page props
  const pageErrors = page.props.errors;
  console.log(categories);

  const [parts, setParts] = useState<RachmaPart[]>([
    { name_ar: '', name_fr: '', length: '', height: '', stitches: '' }
  ]);

  // Parts suggestions state
  const [suggestions, setSuggestions] = useState<PartsSuggestion[]>(partsSuggestions);
  const [showSuggestions, setShowSuggestions] = useState<{ [key: number]: boolean }>({});
  const [filteredSuggestions, setFilteredSuggestions] = useState<{ [key: number]: PartsSuggestion[] }>({});

  const { data, setData, post, processing, errors } = useForm<{
    title_ar: string;
    title_fr: string;
    description_ar: string;
    description_fr: string;
    categories: number[];
    color_numbers: string;
    price: string;
    files: File[];
    preview_images: File[];
    [key: string]: string | number[] | File[] | string[];
  }>({
    title_ar: '',
    title_fr: '',
    description_ar: '',
    description_fr: '',
    categories: [],
    color_numbers: '',
    price: '',
    files: [],
    preview_images: [],
  });

  // Step navigation functions
  const goToNextStep = () => {
    if (currentStep < totalSteps) {
      setCurrentStep(currentStep + 1);
    }
  };

  const goToPreviousStep = () => {
    if (currentStep > 1) {
      setCurrentStep(currentStep - 1);
    }
  };

  // Check if current step is valid before allowing next
  const isCurrentStepValid = () => {
    switch (currentStep) {
      case 1:
        return data.title_ar && data.title_fr && data.categories.length > 0 && data.color_numbers && data.price;
      case 2:
        return parts.every(part => part.name_ar && part.name_fr && part.length && part.height && part.stitches);
      case 3:
        return data.files.length > 0;
      default:
        return false;
    }
  };

  // Get step title
  const getStepTitle = () => {
    switch (currentStep) {
      case 1:
        return 'معلومات الرشمة';
      case 2:
        return 'أجزاء الرشمة';
      case 3:
        return 'الملفات والصور';
      default:
        return '';
    }
  };

  // Add warning message component
  const WarningMessage = () => (
    <Alert variant="warning" className="mb-6">
      <AlertTriangle className="h-4 w-4" />
      <AlertDescription>
        تنبيه: لا يمكن تعديل الرشمة بعد إنشائها. يرجى التأكد من صحة جميع المعلومات قبل الحفظ.
      </AlertDescription>
    </Alert>
  );

  // Fetch parts suggestions if not provided
  useEffect(() => {
    if (!partsSuggestions.length) {
      fetch('/admin/api/parts-suggestions/active')
        .then(response => response.json())
        .then(data => {
          setSuggestions(data);
        })
        .catch(error => {
          console.error('Failed to fetch parts suggestions:', error);
        });
    }
  }, [partsSuggestions]);

  // Filter suggestions based on input
  const filterSuggestions = (partIndex: number, query: string) => {
    if (!query.trim()) {
      setFilteredSuggestions(prev => ({ ...prev, [partIndex]: [] }));
      return;
    }

    const filtered = suggestions.filter(suggestion =>
      suggestion.name_ar.toLowerCase().includes(query.toLowerCase()) ||
      suggestion.name_fr.toLowerCase().includes(query.toLowerCase())
    );

    setFilteredSuggestions(prev => ({ ...prev, [partIndex]: filtered }));
  };

  // Handle part name input change with suggestions
  const handlePartNameChange = (index: number, value: string) => {
    updatePart(index, 'name_ar', value);
    // Show suggestions only if there are matches, allow custom names if no matches
    filterSuggestions(index, value);
    setShowSuggestions(prev => ({ ...prev, [index]: value.length > 0 }));
  };

  // Select a suggestion - fills both Arabic and French names from DB
  const selectSuggestion = (partIndex: number, suggestion: PartsSuggestion) => {
    // Update both fields simultaneously
    const newParts = parts.map((part, i) =>
      i === partIndex
        ? { ...part, name_ar: suggestion.name_ar, name_fr: suggestion.name_fr }
        : part
    );
    setParts(newParts);

    // Hide suggestions
    setShowSuggestions(prev => ({ ...prev, [partIndex]: false }));
    setFilteredSuggestions(prev => ({ ...prev, [partIndex]: [] }));
  };

  // Form change handlers
  function handleChange(e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) {
    setData(e.target.id as keyof typeof data, e.target.value);
  }

  function handleSelectChange(field: keyof typeof data, value: string | number[]) {
    setData(field, value);
  }

  // File handling functions
  function handleRachmaFilesChange(e: React.ChangeEvent<HTMLInputElement>) {
    if (e.target.files) {
      const filesArray = Array.from(e.target.files);
      setData('files', [...data.files, ...filesArray]);
      // Reset the input value so selecting the same file again will trigger onChange
      e.target.value = '';
    }
  }

  function handlePreviewFilesChange(e: React.ChangeEvent<HTMLInputElement>) {
    if (e.target.files) {
      const filesArray = Array.from(e.target.files);
      setData('preview_images', [...data.preview_images, ...filesArray]);
      // Reset the input value so selecting the same file again will trigger onChange
      e.target.value = '';
    }
  }

  // Remove a file from the files array
  function removeRachmaFile(index: number) {
    const updatedFiles = [...data.files];
    updatedFiles.splice(index, 1);
    setData('files', updatedFiles);
  }

  // Remove a preview image from the preview_images array
  function removePreviewFile(index: number) {
    const updatedFiles = [...data.preview_images];
    updatedFiles.splice(index, 1);
    setData('preview_images', updatedFiles);
  }

  // Parts management functions
  const addPart = () => {
    const newParts = [...parts, { name_ar: '', name_fr: '', length: '', height: '', stitches: '' }];
    setParts(newParts);
  };

  const removePart = (index: number) => {
    if (parts.length > 1) {
      const newParts = parts.filter((_, i) => i !== index);
      setParts(newParts);
    }
  };

  const updatePart = (index: number, field: keyof RachmaPart, value: string) => {
    const newParts = parts.map((part, i) =>
      i === index ? { ...part, [field]: value } : part
    );
    setParts(newParts);
  };

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    const formData = new FormData();

    // Add basic form fields
    formData.append('title_ar', data.title_ar);
    formData.append('title_fr', data.title_fr);
    formData.append('description_ar', data.description_ar);
    formData.append('description_fr', data.description_fr);
    formData.append('color_numbers', data.color_numbers);
    formData.append('price', data.price);

    // Add categories
    data.categories.forEach(id => {
      formData.append('categories[]', id.toString());
    });

    // Add files
    data.files.forEach(file => {
      formData.append('files[]', file);
    });

    // Add preview images
    data.preview_images.forEach(file => {
      formData.append('preview_images[]', file);
    });

    // Add parts
    parts.forEach((part, index) => {
      formData.append(`parts[${index}][name_ar]`, part.name_ar);
      formData.append(`parts[${index}][name_fr]`, part.name_fr);
      formData.append(`parts[${index}][length]`, part.length || '');
      formData.append(`parts[${index}][height]`, part.height || '');
      formData.append(`parts[${index}][stitches]`, part.stitches);
    });

    post(route('designer.rachmat.store'), {
      forceFormData: true,
      onSuccess: () => {
        // Reset form
        setParts([{ name_ar: '', name_fr: '', length: '', height: '', stitches: '' }]);
        setCurrentStep(1);
        console.log('success');
      },
      onError: (errors) => {
        // Check if there are file errors
        const fileErrors = Object.keys(errors).filter(key => key.startsWith('files.') || key.startsWith('preview_images.'));
        if (fileErrors.length > 0) {
          console.error(errors[fileErrors[0]]);
        } else if (Object.keys(errors).length > 0) {
          console.error('حدث خطأ أثناء إنشاء الرشمة');
        }
      }
    });
  }

  // Render step indicator
  const renderStepIndicator = () => (
    <div className="flex items-center justify-center mb-8">
      <div className="flex items-center space-x-4 space-x-reverse">
        {[1, 2, 3].map((step) => (
          <div key={step} className="flex items-center">
            <div className={`w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-colors ${step === currentStep
                ? 'bg-primary text-primary-foreground shadow-lg'
                : step < currentStep
                  ? 'bg-green-500 text-white'
                  : 'bg-muted text-muted-foreground'
              }`}>
              {step < currentStep ? '✓' : step}
            </div>
            {step < 3 && (
              <div className={`w-16 h-0.5 mx-2 transition-colors ${step < currentStep ? 'bg-green-500' : 'bg-muted'
                }`} />
            )}
          </div>
        ))}
      </div>
    </div>
  );

  return (
    <AppLayout>
      <Head title="إضافة رشمة جديدة" />

      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/10">
        <div className="container mx-auto p-4 md:p-8 space-y-8">
          {/* Enhanced Header */}
          <DesignerPageHeader
            title="إضافة رشمة جديدة"
            subtitle={`الخطوة ${currentStep} من ${totalSteps}: ${getStepTitle()}`}
            icon={Sparkles}
          >
            <Link href={route('designer.rachmat.index')}>
              <Button variant="outline" size="sm" className="gap-2 hover:bg-muted/50 transition-colors">
                <ArrowLeft className="w-4 h-4" />
                عودة
              </Button>
            </Link>
          </DesignerPageHeader>

          {/* Error Summary */}
          {(Object.keys(errors).length > 0 || Object.keys(pageErrors || {}).length > 0) && (
            <Card className="border-destructive bg-destructive/5">
              <CardContent className="pt-6">
                <div className="flex items-start gap-3">
                  <div className="w-6 h-6 bg-destructive rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span className="text-destructive-foreground text-sm font-bold">!</span>
                  </div>
                  <div className="space-y-2">
                    <h4 className="font-semibold text-destructive">يرجى تصحيح الأخطاء التالية:</h4>
                    <ul className="text-sm text-destructive space-y-1">
                      {Object.entries(errors).map(([, error], index) => (
                        <li key={index} className="flex items-start gap-2">
                          <span className="text-destructive/60">•</span>
                          <span>{error}</span>
                        </li>
                      ))}
                      {Object.entries(pageErrors || {}).map(([, error], index) => (
                        <li key={`page-${index}`} className="flex items-start gap-2">
                          <span className="text-destructive/60">•</span>
                          <span>{error}</span>
                        </li>
                      ))}
                    </ul>
                  </div>
                </div>
              </CardContent>
            </Card>
          )}

          {/* Step Indicator */}
          {renderStepIndicator()}

          {/* Warning Message */}
          <WarningMessage />

          <form onSubmit={handleSubmit} className="space-y-8">
            {/* Step 1: Basic Information */}
            {currentStep === 1 && (
              <Card>
                <CardHeader>
                  <CardTitle className="text-xl font-semibold flex items-center gap-3">
                    <div className="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-md">
                      <FileText className="w-4 h-4 text-white" />
                    </div>
                    معلومات الرشمة
                  </CardTitle>
                </CardHeader>
                <CardContent className="space-y-6">
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div className="md:col-span-2 space-y-2">
                      <Label htmlFor="title_ar" className="font-semibold">عنوان الرشمة (عربي) *</Label>
                      <Input
                        id="title_ar"
                        value={data.title_ar}
                        onChange={handleChange}
                        placeholder="أدخل عنوان الرشمة بالعربية"
                        className="h-11"
                      />
                      {errors.title_ar && <p className="text-sm text-destructive mt-1">{errors.title_ar}</p>}
                    </div>

                    <div className="md:col-span-2 space-y-2">
                      <Label htmlFor="title_fr" className="font-semibold">عنوان الرشمة (فرنسي) *</Label>
                      <Input
                        id="title_fr"
                        value={data.title_fr}
                        onChange={handleChange}
                        placeholder="أدخل عنوان الرشمة بالفرنسية"
                        className="h-11"
                      />
                      {errors.title_fr && <p className="text-sm text-destructive mt-1">{errors.title_fr}</p>}
                    </div>
                  </div>

                  <div className="space-y-2">
                    <Label className="font-semibold">الفئات *</Label>
                    <div className="border rounded-lg p-4 max-h-48 overflow-y-auto">
                      <div className="space-y-3">
                        {categories.map(category => (
                          <div key={category.id} className="flex items-center space-x-3 space-x-reverse">
                            <Checkbox
                              id={`category-${category.id}`}
                              checked={data.categories.includes(category.id)}
                              onCheckedChange={(checked) => {
                                if (checked) {
                                  handleSelectChange('categories', [...data.categories, category.id]);
                                } else {
                                  handleSelectChange('categories', data.categories.filter((id: number) => id !== category.id));
                                }
                              }}
                            />
                            <Label htmlFor={`category-${category.id}`} className="text-sm font-medium cursor-pointer">
                              {`${category.name_ar} / ${category.name_fr}`}
                            </Label>
                          </div>
                        ))}
                      </div>
                    </div>
                    {errors.categories && <p className="text-sm text-destructive mt-1">{errors.categories}</p>}
                  </div>

                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                    <div className="space-y-2">
                      <Label htmlFor="color_numbers" className="font-semibold">عدد الألوان *</Label>
                      <Input
                        type="number"
                        id="color_numbers"
                        value={data.color_numbers}
                        onChange={handleChange}
                        placeholder="مثال: 5"
                        min="1"
                        className="h-11"
                      />
                      {errors.color_numbers && <p className="text-sm text-destructive mt-1">{errors.color_numbers}</p>}
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="price" className="font-semibold">السعر *</Label>
                      <Input
                        type="number"
                        id="price"
                        value={data.price}
                        onChange={handleChange}
                        placeholder="مثال: 50"
                        min="0"
                        step="0.01"
                        className="h-11"
                      />
                      {errors.price && <p className="text-sm text-destructive mt-1">{errors.price}</p>}
                    </div>
                  </div>

                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div className="space-y-2">
                      <Label htmlFor="description_ar" className="font-semibold">وصف الرشمة (عربي)</Label>
                      <Textarea
                        id="description_ar"
                        rows={4}
                        value={data.description_ar}
                        onChange={handleChange}
                        placeholder="أدخل وصف مفصل للرشمة بالعربية..."
                        className="resize-none"
                      />
                      {errors.description_ar && <p className="text-sm text-destructive mt-1">{errors.description_ar}</p>}
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="description_fr" className="font-semibold">وصف الرشمة (فرنسي)</Label>
                      <Textarea
                        id="description_fr"
                        rows={4}
                        value={data.description_fr}
                        onChange={handleChange}
                        placeholder="أدخل وصف مفصل للرشمة بالفرنسية..."
                        className="resize-none"
                      />
                      {errors.description_fr && <p className="text-sm text-destructive mt-1">{errors.description_fr}</p>}
                    </div>
                  </div>
                </CardContent>
              </Card>
            )}

            {/* Step 2: Rachma Parts */}
            {currentStep === 2 && (
              <Card>
                <CardHeader>
                  <div className="flex items-center justify-between">
                    <CardTitle className="text-xl font-semibold flex items-center gap-3">
                      <div className="w-8 h-8 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-md">
                        <Package className="w-4 h-4 text-white" />
                      </div>
                      أجزاء الرشمة *
                    </CardTitle>
                    <Button type="button" variant="outline" size="sm" onClick={addPart} className="gap-2">
                      <Plus className="w-4 h-4" />
                      إضافة جزء
                    </Button>
                  </div>
                </CardHeader>
                <CardContent className="space-y-4">
                  {parts.map((part, index) => (
                    <div key={index} className="border rounded-lg p-4 space-y-4 bg-muted/30">
                      <div className="flex items-center justify-between">
                        <h4 className="font-semibold text-md flex items-center gap-2">
                          <div className="w-6 h-6 bg-primary rounded-md flex items-center justify-center">
                            <span className="text-xs text-primary-foreground font-bold">{index + 1}</span>
                          </div>
                          الجزء {index + 1}
                        </h4>
                        {parts.length > 1 && (
                          <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            onClick={() => removePart(index)}
                            className="text-destructive hover:text-destructive hover:bg-destructive/10"
                          >
                            <Minus className="w-4 h-4 mr-1" />
                            حذف
                          </Button>
                        )}
                      </div>
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div className="relative space-y-2">
                          <Label htmlFor={`part_name_ar_${index}`} className="text-sm font-semibold">اسم الجزء (عربي) *</Label>
                          <Input
                            id={`part_name_ar_${index}`}
                            value={part.name_ar}
                            onChange={e => handlePartNameChange(index, e.target.value)}
                            onFocus={() => {
                              if (part.name_ar) {
                                filterSuggestions(index, part.name_ar);
                                setShowSuggestions(prev => ({ ...prev, [index]: true }));
                              }
                            }}
                            onBlur={() => {
                              setTimeout(() => {
                                setShowSuggestions(prev => ({ ...prev, [index]: false }));
                              }, 200);
                            }}
                            placeholder="اكتب اسم الجزء أو اختر من الاقتراحات..."
                            className="h-22"
                          />
                          <ChevronDown className="absolute left-3 top-9 h-4 w-4 text-muted-foreground" />

                          {showSuggestions[index] && (
                            <div className="absolute z-10 w-full mt-1 bg-card border rounded-lg shadow-lg max-h-48 overflow-y-auto">
                              {filteredSuggestions[index]?.length > 0 ? (
                                filteredSuggestions[index].map((suggestion) => (
                                  <button
                                    key={suggestion.id}
                                    type="button"
                                    onClick={() => selectSuggestion(index, suggestion)}
                                    className="w-full px-4 py-2 text-right hover:bg-muted/50 flex items-center justify-between group transition-colors"
                                  >
                                    <span className="text-sm text-muted-foreground group-hover:text-foreground">
                                      {suggestion.name_fr}
                                    </span>
                                    <span className="text-sm font-medium">
                                      {suggestion.name_ar}
                                    </span>
                                  </button>
                                ))
                              ) : part.name_ar.length > 0 ? (
                                <div className="px-4 py-3 text-sm text-muted-foreground text-center">
                                  <p>لا توجد اقتراحات مطابقة</p>
                                  <p className="text-xs mt-1">يمكنك إدخال اسم مخصص</p>
                                </div>
                              ) : (
                                suggestions.slice(0, 5).map((suggestion) => (
                                  <button
                                    key={suggestion.id}
                                    type="button"
                                    onClick={() => selectSuggestion(index, suggestion)}
                                    className="w-full px-4 py-2 text-right hover:bg-muted/50 flex items-center justify-between group transition-colors"
                                  >
                                    <span className="text-sm text-muted-foreground group-hover:text-foreground">
                                      {suggestion.name_fr}
                                    </span>
                                    <span className="text-sm font-medium">
                                      {suggestion.name_ar}
                                    </span>
                                  </button>
                                ))
                              )}
                            </div>
                          )}

                          {!part.name_ar && (
                            <div className="absolute top-8 left-0 right-0 h-11 bg-transparent flex items-center px-3">
                              <button
                                type="button"
                                onClick={() => {
                                  setFilteredSuggestions(prev => ({ ...prev, [index]: suggestions }));
                                  setShowSuggestions(prev => ({ ...prev, [index]: true }));
                                }}
                                className="text-xs text-muted-foreground hover:text-foreground"
                              >
                                اضغط لعرض الاقتراحات
                              </button>
                            </div>
                          )}

                          {errors[`parts.${index}.name_ar` as keyof typeof errors] && (
                            <p className="text-sm text-destructive mt-1">
                              {errors[`parts.${index}.name_ar` as keyof typeof errors]}
                            </p>
                          )}
                        </div>
                        <div className="space-y-2">
                          <Label htmlFor={`part_name_fr_${index}`} className="text-sm font-semibold">اسم الجزء (فرنسي) *</Label>
                          <Input
                            id={`part_name_fr_${index}`}
                            value={part.name_fr}
                            onChange={e => updatePart(index, 'name_fr', e.target.value)}
                            placeholder="Nom de la partie en français"
                            className="h-11"
                          />
                          <p className="text-xs text-muted-foreground">يملأ تلقائياً من الاقتراحات أو أدخله يدوياً</p>
                          {errors[`parts.${index}.name_fr` as keyof typeof errors] && (
                            <p className="text-sm text-destructive mt-1">
                              {errors[`parts.${index}.name_fr` as keyof typeof errors]}
                            </p>
                          )}
                        </div>
                        <div className="space-y-2">
                          <Label htmlFor={`part_stitches_${index}`} className="text-sm font-semibold">عدد الغرز *</Label>
                          <Input
                            id={`part_stitches_${index}`}
                            type="number"
                            value={part.stitches}
                            onChange={e => updatePart(index, 'stitches', e.target.value)}
                            placeholder="1000"
                            min="1"
                            className="h-11"
                          />
                          {errors[`parts.${index}.stitches` as keyof typeof errors] && (
                            <p className="text-sm text-destructive mt-1">
                              {errors[`parts.${index}.stitches` as keyof typeof errors]}
                            </p>
                          )}
                        </div>
                        <div className="space-y-2">
                          <Label htmlFor={`part_length_${index}`} className="text-sm font-semibold">الطول (سم) *</Label>
                          <Input
                            id={`part_length_${index}`}
                            type="number"
                            step="0.1"
                            value={part.length}
                            onChange={e => updatePart(index, 'length', e.target.value)}
                            placeholder="10.5"
                            min="0.1"
                            className="h-11"
                          />
                          {errors[`parts.${index}.length` as keyof typeof errors] && (
                            <p className="text-sm text-destructive mt-1">
                              {errors[`parts.${index}.length` as keyof typeof errors]}
                            </p>
                          )}
                        </div>
                        <div className="space-y-2">
                          <Label htmlFor={`part_height_${index}`} className="text-sm font-semibold">الارتفاع (سم) *</Label>
                          <Input
                            id={`part_height_${index}`}
                            type="number"
                            step="0.1"
                            value={part.height}
                            onChange={e => updatePart(index, 'height', e.target.value)}
                            placeholder="8.2"
                            min="0.1"
                            className="h-11"
                          />
                          {errors[`parts.${index}.height` as keyof typeof errors] && (
                            <p className="text-sm text-destructive mt-1">
                              {errors[`parts.${index}.height` as keyof typeof errors]}
                            </p>
                          )}
                        </div>
                      </div>
                    </div>
                  ))}
                </CardContent>
              </Card>
            )}

            {/* Step 3: Files & Images */}
            {currentStep === 3 && (
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {/* Rachma Files Upload */}
                <Card>
                  <CardHeader>
                    <CardTitle className="text-xl font-semibold flex items-center gap-3">
                      <div className="w-8 h-8 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center shadow-md">
                        <Package className="w-4 h-4 text-white" />
                      </div>
                      ملفات الرشمة *
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-6">
                    <div className="space-y-3">
                      <Label htmlFor="files" className="font-semibold">إضافة ملفات الرشمة</Label>
                      <Input
                        id="files"
                        type="file"
                        multiple
                        onChange={handleRachmaFilesChange}
                        accept=".zip,.rar,.dst,.exp,.jef,.pes,.vp3,.xxx,.hus,.vip,.sew,.csd,.pdf"
                        className="h-11 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-primary-foreground hover:file:bg-primary/90"
                      />
                      <div className="text-xs text-muted-foreground pt-2">
                        <p>الأنواع المدعومة: .zip, .rar, .dst, .pdf, ...</p>
                        <p>حجم أقصى: 10 ميجابايت لكل ملف</p>
                      </div>
                      {errors.files && <p className="text-destructive text-sm">{errors.files}</p>}

                      {Object.keys(pageErrors || {}).filter(key => key.startsWith('files.')).map((key, index) => (
                        <p key={index} className="text-destructive text-sm">{pageErrors[key]}</p>
                      ))}
                    </div>

                    {data.files.length > 0 && (
                      <div className="space-y-3">
                        <p className="text-sm font-semibold flex items-center gap-2">
                          <Package className="w-4 h-4" />
                          ملفات الرشمة المختارة ({data.files.length}):
                        </p>
                        <div className="space-y-2 max-h-48 overflow-y-auto border rounded-md p-2">
                          {data.files.map((file, index) => (
                            <div key={index} className="flex items-center justify-between bg-muted/50 p-2 rounded-md">
                              <span className="text-sm font-medium truncate">{file.name}</span>
                              <button
                                type="button"
                                onClick={() => removeRachmaFile(index)}
                                className="text-destructive text-xs hover:bg-destructive/10 px-2 py-1 rounded transition-colors font-medium"
                              >
                                إزالة
                              </button>
                            </div>
                          ))}
                        </div>
                      </div>
                    )}
                  </CardContent>
                </Card>

                {/* Preview Images Upload */}
                <Card>
                  <CardHeader>
                    <CardTitle className="text-xl font-semibold flex items-center gap-3">
                      <div className="w-8 h-8 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center shadow-md">
                        <ImageIcon className="w-4 h-4 text-white" />
                      </div>
                      صور المعاينة
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-6">
                    <div className="space-y-3">
                      <Label htmlFor="preview_images" className="font-semibold">إضافة صور المعاينة</Label>
                      <Input
                        id="preview_images"
                        type="file"
                        multiple
                        onChange={handlePreviewFilesChange}
                        accept="image/jpeg,image/png,image/jpg,image/webp"
                        className="h-11 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-primary-foreground hover:file:bg-primary/90"
                      />
                      <div className="text-xs text-muted-foreground pt-2">
                        <p>الأنواع المدعومة: .jpeg, .png, .jpg, .webp</p>
                        <p>حجم أقصى: 2 ميجابايت لكل صورة</p>
                      </div>
                      {errors.preview_images && <p className="text-destructive text-sm">{errors.preview_images}</p>}

                      {Object.keys(pageErrors || {}).filter(key => key.startsWith('preview_images.')).map((key, index) => (
                        <p key={index} className="text-destructive text-sm">{pageErrors[key]}</p>
                      ))}
                    </div>

                    {data.preview_images.length > 0 && (
                      <div className="space-y-3">
                        <p className="text-sm font-semibold flex items-center gap-2">
                          <ImageIcon className="w-4 h-4" />
                          صور المعاينة المختارة ({data.preview_images.length}):
                        </p>
                        <div className="space-y-2 max-h-48 overflow-y-auto border rounded-md p-2">
                          {data.preview_images.map((file, index) => (
                            <div key={index} className="flex items-center justify-between bg-muted/50 p-2 rounded-md">
                              <span className="text-sm font-medium truncate">{file.name}</span>
                              <button
                                type="button"
                                onClick={() => removePreviewFile(index)}
                                className="text-destructive text-xs hover:bg-destructive/10 px-2 py-1 rounded transition-colors font-medium"
                              >
                                إزالة
                              </button>
                            </div>
                          ))}
                        </div>
                      </div>
                    )}
                  </CardContent>
                </Card>
              </div>
            )}

            {/* Navigation Buttons */}
            <div className="flex justify-between items-center pt-6">
              <div>
                {currentStep > 1 && (
                  <Button
                    type="button"
                    variant="outline"
                    onClick={goToPreviousStep}
                    className="gap-2"
                  >
                    <ArrowLeft className="w-4 h-4" />
                    السابق
                  </Button>
                )}
              </div>

              <div>
                {currentStep < totalSteps ? (
                  <Button
                    type="button"
                    onClick={goToNextStep}
                    disabled={!isCurrentStepValid()}
                    className="gap-2"
                  >
                    التالي
                    <ArrowRight className="w-4 h-4" />
                  </Button>
                ) : (
                  <Button
                    type="submit"
                    disabled={processing || !isCurrentStepValid()}
                    className="w-full md:w-auto h-12 text-base font-semibold"
                    size="lg"
                  >
                    {processing ? (
                      <>
                        <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2"></div>
                        جاري الحفظ...
                      </>
                    ) : (
                      <>
                        <Sparkles className="w-5 h-5 mr-2" />
                        حفظ الرشمة
                      </>
                    )}
                  </Button>
                )}
              </div>
            </div>
          </form>
        </div>
      </div>
    </AppLayout>
  );
} 